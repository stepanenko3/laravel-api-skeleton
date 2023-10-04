<?php

namespace Stepanenko3\LaravelApiSkeleton\Relations;

use Stepanenko3\LaravelApiSkeleton\Contracts\QueryBuilder;
use Stepanenko3\LaravelApiSkeleton\Contracts\RelationSchema;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;

class BelongsTo extends Relation implements RelationSchema
{
    /**
     * Perform actions before mutating the MorphTo relation.
     */
    public function beforeMutating(
        Model $model,
        Relation $relation,
        array $mutationRelations,
    ): void {
        $model
            ->{$relation->relation}()
            ->{$mutationRelations[$relation->relation]['operation'] === 'detach' ? 'dissociate' : 'associate'}(
                app()->make(QueryBuilder::class, ['schema' => $relation->schema()])
                    ->applyMutation($mutationRelations[$relation->relation])
            );
    }
}
