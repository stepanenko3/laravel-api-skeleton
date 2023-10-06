<?php

namespace Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks;

use Closure;
use Stepanenko3\LaravelApiSkeleton\DTO\ApplySchemaDTO;
use Stepanenko3\LaravelApiSkeleton\Processes\Task;

class MutateDTO extends Task
{
    public function handle(
        ApplySchemaDTO $payload,
        Closure $next,
    ): mixed {
        return $next(
            $payload->schema->mutateDTO(
                dto: $payload,
            ),
        );
    }
}
