<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Schemas;

use Stepanenko3\LaravelApiSkeleton\Helpers\ExtendedCollection;

class SchemaCollection extends ExtendedCollection
{
    public function __construct(
        array $fields,
        array $with,
        array $withCount,
    ) {
        parent::__construct(
            items: [
                'fields' => $fields,
                'with' => $with,
                'withCount' => $withCount,
            ],
        );
    }

    public function getFields(
        ?array $default = [],
    ): array {
        if ($this->isEmpty(
            key: 'fields',
        )) {
            return $default;
        }

        return $this->get(
            key: 'fields',
        );
    }

    public function hasField(string $field): bool
    {
        return in_array(
            needle: $field,
            haystack: $this->get(
                key: 'fields',
                default: [],
            ),
        );
    }

    public function fields(array $fields): self
    {
        $this->put(
            key: 'fields',
            value: array_merge(
                $this->get(
                    key: 'fields',
                ),
                $fields,
            ),
        );

        return $this;
    }

    public function getRelations(
        ?array $default = [],
    ): array {
        if ($this->isEmpty(
            key: 'with',
        )) {
            return $default;
        }

        return $this->get(
            key: 'with',
        );
    }

    public function hasRelation(string $relation): bool
    {
        return array_key_exists(
            key: $relation,
            array: $this->get(
                key: 'with',
                default: [],
            ),
        );
    }

    public function with(array $relations): self
    {
        $relationsStore = $this->get(
            key: 'with',
            default: [],
        );

        foreach ($relations as $relation => $with) {
            if (array_key_exists(
                key: $relation,
                array: $relationsStore,
            )) {
                continue;
            }

            $relationsStore[$relation] = $with;
        }

        $this->put(
            key: 'with',
            value: $relationsStore,
        );

        return $this;
    }

    public function getCountRelations(
        ?array $default = [],
    ): array {
        if ($this->isEmpty(
            key: 'withCount',
        )) {
            return $default;
        }

        return $this->get(
            key: 'withCount',
        );
    }

    public function hasCountRelation(string $relation): bool
    {
        return in_array(
            needle: $relation,
            haystack: $this->get(
                key: 'withCount',
                default: [],
            ),
        );
    }

    public function withCount(array $relations): self
    {
        $this->put(
            key: 'withCount',
            value: array_merge(
                $this->get(
                    key: 'withCount',
                ),
                $relations,
            ),
        );

        return $this;
    }

    public function toArray(): array
    {
        return [
            'fields' => $this->get(
                key: 'fields',
                default: [],
            ),
            'with' => $this->get(
                key: 'with',
                default: [],
            ),
            'withCount' => $this->get(
                key: 'withCount',
                default: [],
            ),
        ];
    }
}
