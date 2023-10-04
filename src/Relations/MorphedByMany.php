<?php

namespace Stepanenko3\LaravelApiSkeleton\Relations;

use Illuminate\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Concerns\Relations\HasPivotFields;
use Stepanenko3\LaravelApiSkeleton\Contracts\QueryBuilder;
use Stepanenko3\LaravelApiSkeleton\Contracts\RelationSchema;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;
use Stepanenko3\LaravelApiSkeleton\Relations\Traits\HasMultipleResults;
use Stepanenko3\LaravelApiSkeleton\Rules\ArrayWith;

class MorphedByMany extends MorphRelation implements RelationSchema
{
    use HasMultipleResults;
    use HasPivotFields;

    /**
     * Define validation rules for the MorphedByMany relation.
     *
     * @param schema $schema the schema associated with the relation
     * @param string $prefix the prefix used for validation rules
     *
     * @return array an array of validation rules
     */
    public function rules(Schema $schema, string $prefix)
    {
        return array_merge(
            parent::rules($schema, $prefix),
            [
                $prefix . '.*.pivot' => [
                    'prohibited_if:' . $prefix . '.*.operation,detach',
                    new ArrayWith($this->getPivotFields()),
                ],
            ]
        );
    }

    /**
     * Handle actions after mutating a MorphedByMany relation.
     *
     * @param Model $model the Eloquent model
     * @param Relation $relation the relation being mutated
     * @param array $mutationRelations an array of mutation relations
     */
    public function afterMutating(Model $model, Relation $relation, array $mutationRelations): void
    {
        foreach ($mutationRelations[$relation->relation] as $mutationRelation) {
            if ($mutationRelation['operation'] === 'detach') {
                $model
                    ->{$relation->relation}()
                    ->detach(
                        app()->make(QueryBuilder::class, ['schema' => $relation->schema()])
                            ->applyMutation($mutationRelation)
                            ->getKey()
                    );
            } else {
                $model
                    ->{$relation->relation}()
                    ->attach(
                        [
                            app()->make(QueryBuilder::class, ['schema' => $relation->schema()])
                                ->applyMutation($mutationRelation)
                                ->getKey() => $mutationRelation['pivot'] ?? [],
                        ]
                    );
            }
        }
    }
}
