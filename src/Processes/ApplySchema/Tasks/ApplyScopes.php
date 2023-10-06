<?php

namespace Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks;

use Closure;
use Stepanenko3\LaravelApiSkeleton\DTO\ApplySchemaDTO;
use Stepanenko3\LaravelApiSkeleton\Processes\Task;

class ApplyScopes extends Task
{
    public function handle(
        ApplySchemaDTO $payload,
        Closure $next,
    ): mixed {
        if (empty($payload->scopes)) {
            return $next($payload);
        }

        foreach ($payload->scopes as $scope) {
            $payload->builder
                ->{$scope['name']}(
                    ...($scope['parameters'] ?? []),
                );
        }

        return $next($payload);
    }
}
