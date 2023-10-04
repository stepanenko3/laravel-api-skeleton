<?php

namespace Stepanenko3\LaravelApiSkeleton\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Contracts\QueryBuilder;
use Stepanenko3\LaravelApiSkeleton\Contracts\RelationSchema;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

class MorphTo extends MorphRelation implements RelationSchema
{
    /**
     * Create a new MorphTo instance.
     *
     * @param string $relation the name of the relation
     * @param array $types an array of allowed types for the relation
     */
    public function __construct($relation, array $types)
    {
        $this->relation = $relation;
        $this->types = $types;
    }

    /**
     * Perform actions before mutating the MorphTo relation.
     *
     * @param Model $model the Eloquent model
     * @param Relation $relation the relation being mutated
     * @param array $mutationRelations an array of mutation relations
     */
    public function beforeMutating(Model $model, Relation $relation, array $mutationRelations): void
    {
        $model
            ->{$relation->relation}()
            ->{$mutationRelations[$relation->relation]['operation'] === 'detach' ? 'dissociate' : 'associate'}(
                app()->make(QueryBuilder::class, ['schema' => new $mutationRelations[$relation->relation]['type']()])
                    ->applyMutation($mutationRelations[$relation->relation])
            );
    }

    /**
     * Define validation rules for the MorphTo relation.
     *
     * @param schema $schema the schema associated with the relation
     * @param string $prefix the prefix used for validation rules
     *
     * @return array an array of validation rules
     */
    public function rules(Schema $schema, string $prefix)
    {
        return [
            ...parent::rules($schema, $prefix),
            $prefix . '.type' => [
                'required_with:' . $prefix,
                Rule::in(
                    $this->types
                ),
            ],
        ];
    }
}
