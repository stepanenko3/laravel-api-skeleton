<?php

namespace Stepanenko3\LaravelApiSkeleton\Http;

use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Rules\KeysIn;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\Rules\ArrayOrTrue;

abstract class Schema
{
    public static function defaultFields(): array
    {
        return static::fields();
    }

    public static function defaultRelations(): array
    {
        return [];
    }

    public static function defaultCountRelations(): array
    {
        return [];
    }

    public static function applyLogic(
        array $fields,
        array $with,
        array $withCount,
    ): array {
        return [
            $fields,
            $with,
            $withCount,
        ];
    }

    public static function basicFields(): array
    {
        if (property_exists(static::class, 'basicFields')) {
            return static::$basicFields;
        }

        return [];
    }

    public static function rules(): array
    {
        return array_merge(
            [
                'fields' => [
                    'nullable',
                    'array',
                ],
                'fields.*' => [
                    'required',
                    'string',
                    Rule::in(
                        static::fields(),
                    ),
                ],
                'with' => [
                    'nullable',
                    'array',
                ],
                'with_count' => [
                    'nullable',
                    'array',
                    Rule::in(
                        static::countRelations(),
                    ),
                ],
            ],
            static::schemaRelationsToRules(
                relations: static::relations(),
            ),
        );
    }

    public static function applyToQuery(
        EloquentBuilder | QueryBuilder | Builder $builder,
        array $fields = [],
        array $with = [],
        array $withCount = [],
    ): EloquentBuilder | QueryBuilder {
        $table = $builder->getModel()->getTable();

        [$fields, $with, $withCount] = static::applyLogic(
            fields: array_merge(
                static::basicFields(),
                $fields ?: static::defaultFields(),
            ),
            with: static::getRelationsFromSchema(
                relations: $with,
                schemaClass: static::class,
            ),
            withCount: $withCount ?: static::defaultCountRelations(),
        );

        return $builder
            ->when(
                !empty($fields),
                fn ($q) => $q->select(
                    array_map(
                        callback: fn ($value) => $table . '.' . $value,
                        array: $fields,
                    ),
                ),
            )
            ->with($with)
            ->withCount($withCount);
    }

    abstract public static function fields(): array;

    abstract public static function relations(): array;

    abstract public static function countRelations(): array;

    private static function schemaRelationsToRules(
        string $prefix = '',
        array $relations = [],
        int $level = 0,
        int $maxLevel = 1,
    ): array {
        if ($level > $maxLevel) {
            return [];
        }

        $rules = [
            $prefix . 'with' => [
                'nullable',
                'array',
                new KeysIn(
                    values: array_keys($relations),
                ),
            ],
        ];

        $level++;

        foreach ($relations as $relation => $relationClass) {
            $key = $prefix . 'with.' . $relation;

            $allowedFields = $relationClass::fields();
            $allowedRelations = $relationClass::relations();
            $allowedCountRelations = $relationClass::countRelations();

            $rules += [
                $key => [
                    new ArrayOrTrue,
                    new KeysIn(
                        values: ['fields', 'with', 'with_count'],
                    ),
                ],

                $key . '.fields' => [
                    'nullable',
                    'array',
                    Rule::in(
                        values: $allowedFields,
                    ),
                ],
            ];

            if (!empty($allowedRelations)) {
                $rules[$key . '.with'] = [
                    'nullable',
                    'array',
                    'max:' . count($allowedRelations),
                ];

                $rules += static::schemaRelationsToRules(
                    prefix: $key . '.',
                    relations: $allowedRelations,
                    level: $level,
                    maxLevel: $maxLevel,
                );
            }

            if (!empty($allowedCountRelations)) {
                $rules[$key . '.with_count'] = [
                    'nullable',
                    'array',
                    Rule::in(
                        values: static::countRelations(),
                    ),
                ];
            }
        }

        return $rules;
    }

    private static function getRelationsFromSchema(
        array $relations,
        string $schemaClass,
    ): array {
        $allowedRelations = $schemaClass::relations();

        if (empty($relations)) {
            $relations = collect($schemaClass::defaultRelations())
                ->mapWithKeys(fn ($relation) => [$relation => []])
                ->toArray();
        }

        $result = [];

        foreach ($relations as $key => $relation) {
            $relationSchema = $allowedRelations[$key];

            [$fields, $with, $withCount] = $relationSchema::applyLogic(
                fields: array_merge(
                    $relationSchema::basicFields(),
                    ($relation['fields'] ?? []) ?: $relationSchema::defaultFields(),
                ),
                with: static::getRelationsFromSchema(
                    relations: $relation['with'] ?? [],
                    schemaClass: $relationSchema,
                ),
                withCount: ($relation['with_count'] ?? []) ?: $relationSchema::defaultCountRelations(),
            );

            $result[$key] = fn ($q) => $q
                ->when(
                    !empty($fields),
                    function ($q) use ($fields) {
                        $table = $q->getModel()->getTable();

                        return $q->select(
                            array_map(
                                callback: fn ($field) => $table . '.' . $field,
                                array: $fields,
                            )
                        );
                    },
                )
                ->with($with)
                ->withCount($withCount);
        }

        return $result;
    }
}
