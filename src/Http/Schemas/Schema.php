<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Schemas;

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
        SchemaCollection $collection,
    ): SchemaCollection {
        return $collection;
    }

    public static function protectedRelations(): array
    {
        return [];
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

        $collection = static::applyLogic(
            collection: new SchemaCollection(
                fields: $fields,
                with: $with,
                withCount: $withCount,
            )
        );

        $fields = array_merge(
            static::basicFields(),
            $collection->getFields(
                default: static::defaultFields(),
            ),
        );

        return $builder
            ->when(
                value: !empty($fields),
                callback: fn ($builder) => $builder->select(
                    array_map(
                        callback: fn ($value) => $table . '.' . $value,
                        array: $fields,
                    ),
                ),
            )
            ->with(
                static::getRelationsFromSchema(
                    relations: $collection->getRelations(),
                    schemaClass: static::class,
                )
            )
            ->withCount(
                $collection->getCountRelations(
                    default: static::defaultCountRelations(),
                ),
            );
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
                    new ArrayOrTrue(),
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
        $allowedRelations = array_merge(
            $schemaClass::relations(),
            $schemaClass::protectedRelations()
        );

        if (empty($relations)) {
            $relations = collect($schemaClass::defaultRelations())
                ->mapWithKeys(fn ($relation) => [$relation => []])
                ->toArray();
        }

        $result = [];

        foreach ($relations as $key => $relation) {
            $relationSchema = $allowedRelations[$key];

            $collection = $relationSchema::applyLogic(
                collection: new SchemaCollection(
                    fields: $relation['fields'] ?? [],
                    with: $relation['with'] ?? [],
                    withCount: $relation['with_count'] ?? [],
                ),
            );

            $result[$key] = function (EloquentBuilder | QueryBuilder | Builder $q) use ($relationSchema, $collection) {
                $table = $q->getModel()->getTable();

                $fields = array_merge(
                    $relationSchema::basicFields(),
                    $collection->getFields(
                        default: $relationSchema::defaultFields(),
                    ),
                );

                return $q
                    ->when(
                        value: !empty($fields),
                        callback: fn ($builder) => $builder->select(
                            array_map(
                                callback: fn ($field) => $table . '.' . $field,
                                array: $fields,
                            ),
                        )
                    )
                    ->with(
                        static::getRelationsFromSchema(
                            relations: $collection->getRelations(),
                            schemaClass: $relationSchema,
                        ),
                    )
                    ->withCount(
                        $collection->getCountRelations(
                            default: $relationSchema::defaultCountRelations(),
                        ),
                    );
            };
        }

        return $result;
    }
}
