<?php

namespace Stepanenko3\LaravelApiSkeleton\Concerns\Schemas;

use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;

trait ConfiguresRestParameters
{
    use Relationable;

    /**
     * The fields that could be provided.
     */
    public function fields(
        Request $request,
    ): array {
        return [];
    }

    /**
     * Get the resource's fields.
     */
    public function getFields(
        Request $request,
    ): array {
        return $this->fields(
            request: $request,
        );
    }

    public function defaultFields(
        Request $request,
    ): array {
        return [];
    }

    public function getDefaultFields(
        Request $request,
    ): array {
        return $this->defaultFields(
            request: $request,
        );
    }

    /**
     * Get nested fields by prefixing them with a given prefix.
     */
    public function getNestedFields(
        Request $request,
        string $prefix = '',
        array $loadedRelations = [],
    ): array {
        if ($prefix !== '') {
            $prefix = $prefix . '.';
        }

        $fields = array_map(
            callback: fn (string $field) => $prefix . $field,
            array: $this->getFields(
                request: $request
            ),
        );

        $relationsData = collect(
            value: $this->getRelations(
                request: $request,
            ),
        )->filter(
            callback: fn ($relation) => !in_array(
                needle: $relation->relation,
                haystack: $loadedRelations
            ),
        );

        foreach ($relationsData as $relation) {
            $loadedRelations[] = $relation->relation;

            array_push(
                $fields,
                ...$relation
                    ->schema()
                    ->getNestedFields(
                        request: $request,
                        prefix: $prefix . $relation->relation,
                        loadedRelations: $loadedRelations,
                    ),

                // We push the pivot fields if they exists
                ...collect(
                    value: method_exists($relation, 'getPivotFields')
                        ? $relation->getPivotFields()
                        : [],
                )->map(
                    callback: fn ($field) => $prefix . $relation->relation . '.pivot.' . $field,
                )
            );
        }

        return $fields;
    }

    /**
     * The scopes that could be provided.
     */
    public function scopes(
        Request $request,
    ): array {
        return [];
    }

    /**
     * Get the resource's scopes.
     */
    public function getScopes(
        Request $request,
    ): array {
        return $this->scopes(
            request: $request,
        );
    }

    /**
     * The limits that could be provided.
     */
    public function limits(
        Request $request,
    ): array {
        return [
            10,
            25,
            50,
        ];
    }

    /**
     * Get the resource's limits.
     */
    public function getLimits(
        Request $request,
    ): array {
        return $this->limits(
            request: $request,
        );
    }
}
