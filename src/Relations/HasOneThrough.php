<?php

namespace Stepanenko3\LaravelApiSkeleton\Relations;

use Illuminate\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Contracts\RelationSchema;
use Stepanenko3\LaravelApiSkeleton\Http\Schema;

class HasOneThrough extends Relation implements RelationSchema
{
    /**
     * Perform actions after mutating the HasOneThrough relation.
     *
     * @param Model $model the Eloquent model
     * @param Relation $relation the relation being mutated
     * @param array $mutationRelations an array of mutation relations
     */
    public function afterMutating(Model $model, Relation $relation, array $mutationRelations): void
    {
        throw new \RuntimeException('You can\'t mutate a \'HasOneThrough\' relation.');
    }

    /**
     * Define validation rules for the HasOneThrough relation.
     *
     * @param schema $schema the schema associated with the relation
     * @param string $prefix the prefix used for validation rules
     *
     * @return array an array of validation rules
     */
    public function rules(Schema $schema, string $prefix)
    {
        return [
            $prefix => 'prohibited',
        ];
    }
}
