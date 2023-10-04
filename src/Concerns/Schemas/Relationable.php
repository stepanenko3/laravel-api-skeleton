<?php

namespace Stepanenko3\LaravelApiSkeleton\Concerns\Schemas;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;
use Stepanenko3\LaravelApiSkeleton\Relations\Relation;

trait Relationable
{
    /**
     * Get a relation by name.
     */
    public function relation(
        string $name,
    ): Relation | null {
        $name = relation_without_pivot(
            relation: $name,
        );

        $isSubRelation = Str::contains(
            haystack: $name,
            needles: '.',
        );

        $relationName = $isSubRelation
            ? Str::before(
                subject: $name,
                search: '.',
            )
            : $name;

        $relation = Arr::first(
            array: $this->getRelations(
                request: app()->make(
                    abstract: Request::class,
                ),
            ),
            callback: fn ($relation) => $relation->relation === $relationName,
        );

        if (
            $isSubRelation
            && Str::contains(
                haystack: $nestedRelation = Str::after(
                    subject: $name,
                    search: '.',
                ),
                needles: '.',
            )
        ) {
            return $relation->schema()->relation(
                name: $nestedRelation,
            );
        }

        return $relation;
    }

    /**
     * Get the schema associated with a relation by name.
     */
    public function relationSchema(string $name): Schema | null
    {
        return $this
            ->relation(
                name: $name,
            )
            ?->schema();
    }

    /**
     * Get nested relations with their names as keys.
     */
    public function nestedRelations(
        Request $request,
        string $prefix = '',
        array $loadedRelations = [],
    ): array {
        if ($prefix !== '') {
            $prefix = $prefix . '.';
        }

        $relations = [];

        $relationsCollection = collect(
            $this->getRelations(
                request: $request,
            ),
        )->filter(
            fn ($relation) => !in_array(
                needle: $relation->relation,
                haystack: $loadedRelations,
            ),
        );

        foreach ($relationsCollection as $relation) {
            $relations[$prefix . $relation->relation] = $relation;

            $nestedRelations = $relation->schema()->nestedRelations(
                request: $request,
                prefix: $prefix . $relation->relation,
                loadedRelations: array_merge(
                    $loadedRelations,
                    [$relation->relation],
                ),
            );

            foreach ($nestedRelations as $key => $value) {
                $relations[$key] = $value;
            }
        }

        return $relations;
    }

    /**
     * The relations that could be provided.
     */
    public function relations(
        Request $request,
    ): array {
        return [];
    }

    /**
     * Get the relations for the schema.
     */
    public function getRelations(
        Request $request,
    ): array {
        return array_map(
            fn (Relation $relation) => $relation->fromSchema(
                fromSchema: $this,
            ),
            $this->relations(
                request: $request,
            )
        );
    }
}
