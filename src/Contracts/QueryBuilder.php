<?php

namespace Stepanenko3\LaravelApiSkeleton\Contracts;

use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;

interface QueryBuilder
{
    /**
     * Build a "search" query for the given resource.
     */
    public function search(
        array $parameters = []
    ): BuilderContract;

    /**
     * Convert the query builder to an Eloquent query builder.
     */
    public function toBase(): BuilderContract;
}
