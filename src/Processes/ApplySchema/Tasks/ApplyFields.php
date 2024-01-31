<?php

namespace Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks;

use Closure;
use Stepanenko3\LaravelApiSkeleton\DTO\ApplySchemaDTO;
use Stepanenko3\LaravelApiSkeleton\Processes\Task;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilderContract;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder;

class ApplyFields extends Task
{
    public function handle(
        ApplySchemaDTO $payload,
        Closure $next,
    ): mixed {
        $fields = array_merge(
            $payload->schema->basicFields(),
            $payload->fields ?: $payload->schema->defaultFields(),
        );

        $payload->builder
            ->when(
                value: !empty($fields),
                callback: function (EloquentBuilderContract | QueryBuilderContract | Builder $builder) use ($fields): EloquentBuilderContract | QueryBuilderContract | Builder {
                    $table = $builder->getModel()->getTable();

                    $fields = array_unique(
                        array_map(
                            callback: fn ($value) => $table . '.' . $value,
                            array: $fields,
                        ),
                    );

                    return $builder->select(
                        $fields,
                    );
                }
            );

        return $next($payload);
    }
}
