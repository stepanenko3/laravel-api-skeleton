<?php

namespace Stepanenko3\LaravelApiSkeleton\Relations;

use Illuminate\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Contracts\QueryBuilder;
use Stepanenko3\LaravelApiSkeleton\Contracts\RelationSchema;
use Stepanenko3\LaravelApiSkeleton\Relations\Traits\HasMultipleResults;

class HasMany extends Relation implements RelationSchema
{
    use HasMultipleResults;

    /**
     * Perform actions after mutating the HasMany relation.
     *
     * @param Model $model the Eloquent model
     * @param Relation $relation the relation being mutated
     * @param array $mutationRelations an array of mutation relations
     */
    public function afterMutating(Model $model, Relation $relation, array $mutationRelations): void
    {
        foreach ($mutationRelations[$relation->relation] as $mutationRelation) {
            $attributes = [
                $model->{$relation->relation}()->getForeignKeyName() => $mutationRelation['operation'] === 'detach' ? null : $model->{$relation->relation}()->getParentKey(),
            ];

            app()->make(QueryBuilder::class, ['schema' => $relation->schema()])
                ->applyMutation($mutationRelation, $attributes);
        }
    }
}
