<?php

namespace Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks;

use Closure;
use Stepanenko3\LaravelApiSkeleton\DTO\ApplySchemaDTO;
use Stepanenko3\LaravelApiSkeleton\Processes\Task;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilderContract;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\DTO\SchemaDTO;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;
use Stepanenko3\LaravelApiSkeleton\Scopes\ApplySchema;

class ApplyRelations extends Task
{
    public function handle(
        ApplySchemaDTO $payload,
        Closure $next,
    ): mixed {
        $relations = $this->getRelations(
            relations: $payload->with,
            schema: $payload->schema,
        );

        $payload->builder->when(
            value: !empty($relations),
            callback: fn (EloquentBuilderContract | QueryBuilderContract | Builder $query) => $query->with(
                relations: $relations,
            ),
        );

        return $next($payload);
    }

    private function getRelations(
        array $relations,
        Schema $schema,
    ): array {
        $allowedRelations = array_merge(
            $schema->relations(),
            $schema->protectedRelations()
        );

        if (empty($relations)) {
            $relations = array_map(
                array: array_keys(
                    array: $schema->defaultRelations(),
                ),
                callback: fn (string $relation) => [
                    'relation' => $relation,
                ],
            );
        }

        return collect(
            value: $relations,
        )
            ->mapWithKeys(
                callback: fn (array $with) => [
                    $with['relation'] => fn (EloquentBuilderContract | QueryBuilderContract | Builder $query) => $query->tap(
                        new ApplySchema(
                            schema: new $allowedRelations[$with['relation']](),
                            dto: new SchemaDTO(
                                fields: $with['fields'] ?? [],
                                with: $with['with'] ?? [],
                                with_count: $with['with_count'] ?? [],
                            ),
                        ),
                    ),
                ],
            )
            ->toArray();
    }
}
