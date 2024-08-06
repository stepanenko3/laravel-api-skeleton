<?php

namespace Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks;

use Closure;
use Stepanenko3\LaravelApiSkeleton\DTO\ApplySchemaDTO;
use Stepanenko3\LaravelApiSkeleton\Processes\Task;

class ApplySorts extends Task
{
    public function handle(
        ApplySchemaDTO $payload,
        Closure $next,
    ): mixed {
        if (empty($payload->order_by)) {
            $payload->order_by = $payload->schema->defaultSort();
        }

        if (empty($payload->order_by)) {
            return $next($payload);
        }

        $sorts = $payload->schema->sorts();

        $sortFields = $sorts[$payload->order_by] ?? [];

        if (is_callable($sortFields)) {
            $sortFields = $sortFields($payload);
        } else {
            if (is_string($sortFields)) {
                $sortFields = [
                    $sortFields => 'asc',
                ];
            }

            foreach ($sortFields as $field => $direction) {
                if (is_callable($direction)) {
                    $direction($payload->builder);
                } else {
                    $payload->builder->orderBy(
                        $field,
                        $direction,
                    );
                }
            }
        }

        return $next($payload);
    }
}
