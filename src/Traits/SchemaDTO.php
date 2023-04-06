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
        [$fields, $relations, $countRelations] = $this->schema::getAllowed();

        return array_merge(
            [
                'fields' => [
                    'nullable',
                    'array',
                ],
                'fields.*' => [
                    'required',
                    'string',
                    Rule::in($fields),
                ],

                'with' => [
                    'nullable',
                    'array',
                ],
            ],
            $this->relationsToRules($relations),
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
            ->select($this->getFields())
            ->with($this->relationsToQuery());
    }

    public function getFields(): array
    {
        if (empty($this->fields)) {
            return $this->schema::defaultFields();
        }

        return $this->fields;
    }

    private function relationsToQuery(array $relations = []): array
    {
        // Load default relations
        // Load default fields for each relation
        $data = [];

        foreach (($relations ?: $this->with) as $key => $relation) {
            $data[$key] = fn ($q) => $q
                ->select(
                    ...$realtion['fields'] ?? [],
                )
                ->when(
                    !empty($relation['with'] ?? []),
                    fn ($query) => $query->with(
                        $this->relationsToQuery(
                            $relation['with'] ?? [],
                        ),
                    ),
                );
        }

        return $data;
    }

    private function relationsToRules(array $relations, string $prefix = ''): array
    {
        $rules = [
            $prefix . 'with' => [
                'nullable',
                'array',
                new KeysIn(array_keys($relations)),
            ],
        ];

        foreach ($relations as $relationName => $relation) {
            $key = $prefix . 'with.' . $relationName;

            $rules[$key] = [
                'nullable',
                'array',
            ];

            if (!empty($relation['fields'] ?? [])) {
                $rules[$key . '.fields'] = [
                    'required_with:' . $key,
                    'array',
                    Rule::in($relation['fields']),
                ];
            }

            if (!empty($relation['with'] ?? [])) {
                $rules[$key . '.with'] = [
                    'nullable',
                    'array',
                ];

                $rules = array_merge(
                    $rules,
                    $this->relationsToRules(
                        $relation['with'],
                        $key . '.',
                    ),
                );
            }
        }

        return $rules;
    }
}
