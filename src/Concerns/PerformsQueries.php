<?php

namespace Stepanenko3\LaravelApiSkeleton\Concerns;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder as EloquentBuilder;
use Stepanenko3\LaravelApiSkeleton\DTO\DestroyDTO;
use Stepanenko3\LaravelApiSkeleton\DTO\ForceDeleteDTO;
use Stepanenko3\LaravelApiSkeleton\DTO\IncludeDTO;
use Stepanenko3\LaravelApiSkeleton\DTO\MutateDTO;
use Stepanenko3\LaravelApiSkeleton\DTO\RestoreDTO;
use Stepanenko3\LaravelApiSkeleton\DTO\SearchDTO;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;

trait PerformsQueries
{
    /**
     * Build a "search" query for fetching resource.
     */
    public function searchQuery(
        Request $request,
        SearchDTO | IncludeDTO $dto,
        Builder | EloquentBuilder $query,
    ): Builder | EloquentBuilder {
        return $query;
    }

    /**
     * Build a query for mutating resource.
     */
    public function mutateQuery(
        Request $request,
        MutateDTO $dto,
        Builder | EloquentBuilder $query,
    ): Builder | EloquentBuilder {
        return $query;
    }

    /**
     * Build a "destroy" query for the given resource.
     */
    public function destroyQuery(
        Request $request,
        DestroyDTO $dto,
        Builder | EloquentBuilder $query,
    ): Builder | EloquentBuilder {
        return $query;
    }

    /**
     * Build a "restore" query for the given resource.
     */
    public function restoreQuery(
        Request $request,
        RestoreDTO $dto,
        Builder | EloquentBuilder $query,
    ): Builder | EloquentBuilder {
        return $query;
    }

    /**
     * Build a "forceDelete" query for the given resource.
     */
    public function forceDeleteQuery(
        Request $request,
        ForceDeleteDTO $dto,
        Builder $query,
    ): Builder {
        return $query;
    }
}
