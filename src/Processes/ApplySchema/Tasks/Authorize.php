<?php

namespace Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks;

use Closure;
use Stepanenko3\LaravelApiSkeleton\DTO\ApplySchemaDTO;
use Stepanenko3\LaravelApiSkeleton\Processes\Task;

class Authorize extends Task
{
    public function handle(
        ApplySchemaDTO $payload,
        Closure $next,
    ): mixed {
        if ($payload->isAuthorizingEnabled) {
            $payload->schema->authorizeTo(
                ability: 'viewAny',
                model: $payload->builder->getModel(),
            );
        }

        return $next($payload);
    }
}
