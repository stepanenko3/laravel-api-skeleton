<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Schemas;

use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\DTO\Casts\ArrayCast;
use Stepanenko3\LaravelApiSkeleton\Rules\KeysIn;

abstract class SchemaQueryBuilder extends Schema
{
    protected array $fields = [];

    protected array $with = [];

    protected array $with_count = [];

    public function __construct(
        protected array $data,
    ) {
        parent::__construct($data);

        $this->fields = $this->defaultFields();
        $this->with = $this->defaultRelations();
        $this->with_count = $this->defaultCountRelations();
    }

    public function applyToQuery(EloquentBuilder | QueryBuilder $builder): EloquentBuilder | QueryBuilder
    {
        return $builder
            ->select($this->fields)
            ->with($this->relationsToQuery());
    }

    abstract protected function allowedFields(): array;

    abstract protected function allowedRelations(): array;

    abstract protected function allowedCountRelations(): array;

    protected function defaultFields(): array
    {
        return $this->allowedFields();
    }

    protected function defaultRelations(): array
    {
        return [];
    }

    protected function defaultCountRelations(): array
    {
        return $this->allowedCountRelations();
    }

    protected function getRules(): array
    {
        [$fields, $relations, $countRelations] = $this->getAllowed();

        return array_merge(
            $this->rules(),
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

    protected function getCasts(): array
    {
        return array_merge(
            parent::getCasts(),
            [
                'fields' => new ArrayCast(),
                'with' => new ArrayCast(),
                'with_count' => new ArrayCast(),
            ],
        );
    }

    private function relationsToQuery(array $relations = []): array
    {
        $data = [];

        foreach (($this->with ?: $relations) as $key => $relation) {
            $data[$key] = fn ($q) => $q
                ->select($realtion['fields'] ?? [])
                ->with($this->relationsToQuery($relation['with'] ?? []));
        }

        return $data;
    }

    private function getAllowed(
        array $excludedSchemas = [],
    ): array {
        $fields = $this->allowedFields();
        $relations = [];
        $countRelations = $this->allowedCountRelations();

        foreach ($this->allowedRelations() as $name => $class) {
            if (in_array($class, $excludedSchemas)) {
                continue;
            }

            [$allowedFields, $allowedRelations, $allowdCountRelations] = (new $class(standalone: true))
                ->getAllowed(
                    array_unique(
                        array_merge(
                            $excludedSchemas,
                            [static::class],
                        ),
                    ),
                );

            $relations[$name] = [
                'fields' => $allowedFields,
                'with' => $allowedRelations,
            ];
        }

        return [$fields, $relations, $countRelations];
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
