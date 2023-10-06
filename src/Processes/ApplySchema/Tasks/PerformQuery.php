<?php

namespace Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks;

use Closure;
use Stepanenko3\LaravelApiSkeleton\DTO\ApplySchemaDTO;
use Stepanenko3\LaravelApiSkeleton\Processes\Task;

class PerformQuery extends Task
{
    public function handle(
        ApplySchemaDTO $payload,
        Closure $next,
    ): mixed {
        $payload->schema->performQuery(
            query: $payload->builder,
            dto: $payload,
        );

        return $next($payload);
    }
}
