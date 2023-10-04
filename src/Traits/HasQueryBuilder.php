<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder;

trait HasQueryBuilder
{
    /**
     * The query builder instance.
     */
    protected Builder | QueryBuilder $queryBuilder;

    public function newQueryBuilder(
        Schema $schema,
        BuilderContract | null $query = null,
        bool $isAuthorizingEnabled = true,
    ): static {
        return new static(
            request: $this->request,
            schema: $schema,
            query: $query,
            isAuthorizingEnabled: $isAuthorizingEnabled,
        );
    }

    /**
     * Convert the query builder to an Eloquent query builder.
     */
    public function toBase(): Builder
    {
        return $this->queryBuilder;
    }
}
