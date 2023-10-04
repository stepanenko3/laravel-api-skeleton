<?php

namespace Stepanenko3\LaravelApiSkeleton\Relations;

use Illuminate\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Contracts\QueryBuilder;
use Stepanenko3\LaravelApiSkeleton\Contracts\RelationSchema;

class HasOne extends Relation implements RelationSchema
{
    /**
     * Perform actions after mutating the HasOne relation.
     *
     * @param Model $model the Eloquent model
     * @param Relation $relation the relation being mutated
     * @param array $mutationRelations an array of mutation relations
     */
    public function afterMutating(Model $model, Relation $relation, array $mutationRelations): void
    {
        $attributes = [
            $model->{$relation->relation}()->getForeignKeyName() => $mutationRelations[$relation->relation]['operation'] === 'detach' ? null : $model->{$relation->relation}()->getParentKey(),
        ];

        app()->make(QueryBuilder::class, ['schema' => $relation->schema()])
            ->applyMutation($mutationRelations[$relation->relation], $attributes);
    }
}
