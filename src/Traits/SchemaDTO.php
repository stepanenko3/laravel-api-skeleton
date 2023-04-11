<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Exception;
use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\DTO\Casts\ArrayCast;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Stepanenko3\LaravelApiSkeleton\Rules\KeysIn;

trait SchemaDTO
{
    protected array $fields = [];

    protected array $with = [];

    protected array $with_count = [];

    public function bootSchemaDTO(): void
    {
        if (!$this->isPropInitialized('schema')) {
            throw new Exception(static::class . ' schema is required prop');
        }
    }

    public function rulesSchemaDTO(): array
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
                        $this->schema::fields(),
                    ),
                ],

                'with' => [
                    'nullable',
                    'array',
                ],
            ],
            $this->schemaRelationsToRules(
                relations: $this->schema::relations(),
            ),
        );
    }

    public function castsSchemaDTO(): array
    {
        return [
            'fields' => new ArrayCast(),
            'with' => new ArrayCast(),
            'with_count' => new ArrayCast(),
        ];
    }

    public function applyToQuery($builder): EloquentBuilder | QueryBuilder
    {
        return $builder
            ->select(
                $this->fields ?: $this->schema::defaultFields()
            )
            ->with(
                $this->getRelationsFromSchema(
                    relations: $this->with,
                    schemaClass: $this->schema,
                ),
            )
            ->withCount(
                $this->with_count ?: $this->schema::defaultCountRelations(),
            );
    }

    private function schemaRelationsToRules(
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
                new KeysIn(array_keys($relations)),
            ],
        ];

        $level++;

        foreach ($relations as $relation => $relationClass) {
            $key = $prefix . 'with.' . $relation;

            $allowedFields = $relationClass::fields();
            $allowedRelations = $relationClass::relations();

            $rules += [
                $key => [
                    'nullable',
                    'array',
                    'required_array_keys:fields',
                ],

                $key . '.fields' => [
                    'nullable',
                    'array',
                    'max:' . count($allowedFields),
                    Rule::in($allowedFields),
                ],
            ];

            if (!empty($allowedRelations)) {
                $rules[$key . '.with'] = [
                    'nullable',
                    'array',
                    'max:' . count($allowedRelations),
                ];

                $rules += $this->schemaRelationsToRules(
                    prefix: $key . '.',
                    relations: $allowedRelations,
                    level: $level,
                    maxLevel: $maxLevel,
                );
            }
        }

        return $rules;
    }

    private function getRelationsFromSchema(
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
            $result[$key] = fn ($q) => $q
                ->select(
                    ($relation['fields'] ?? []) ?: $allowedRelations[$key]::defaultFields(),
                )
                ->with(
                    $this->getRelationsFromSchema(
                        relations: $relation['with'] ?? [],
                        schemaClass: $allowedRelations[$key],
                    )
                )
                ->withCount(
                    ($relation['with_count'] ?? []) ?: $allowedRelations[$key]::defaultCountRelations(),
                );
        }

        return $result;
    }
}
