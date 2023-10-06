<?php

namespace Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks;

use Closure;
use Stepanenko3\LaravelApiSkeleton\DTO\ApplySchemaDTO;
use Stepanenko3\LaravelApiSkeleton\Processes\Task;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilderContract;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder;

class ApplyCountRelations extends Task
{
    public function handle(
        ApplySchemaDTO $payload,
        Closure $next,
    ): mixed {
        $relations = $payload->with_count ?: $payload->schema->defaultCountRelations();

        $payload->builder
            ->when(
                value: !empty($relations),
                callback: fn (EloquentBuilderContract | QueryBuilderContract | Builder $query) => $query->withCount(
                    relations: $relations,
                ),
            );

        return $next($payload);
    }
}
