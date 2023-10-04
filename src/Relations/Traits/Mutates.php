<?php

namespace Stepanenko3\LaravelApiSkeleton\Relations\Traits;

use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Relations\Relation;

trait Mutates
{
    /**
     * Perform actions before mutating a relation.
     */
    public function beforeMutating(
        Model $model,
        Relation $relation,
        array $mutationRelations,
    ): void {
    }

    /**
     * Perform actions after mutating a relation.
     */
    public function afterMutating(
        Model $model,
        Relation $relation,
        array $mutationRelations,
    ): void {
    }
}
