<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Requests;

use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Http\Resource;
use Stepanenko3\LaravelApiSkeleton\Rules\ArrayWith;
use Stepanenko3\LaravelApiSkeleton\Rules\CustomRulable;

class MutateRequest extends Request
{
    /**
     * Define the validation rules for the mutate request.
     *
     * @return array
     *
     * This method defines the validation rules for mutating resources, such as create, update, attach, or detach.
     * It includes rules for the operation type, attributes, and relations.
     */
    public function rules()
    {
        return $this->mutateRules($this->route()->controller::newResource());
    }

    /**
     * Define the validation rules for mutating resources.
     *
     * @param string $prefix
     * @param array $loadedRelations
     *
     * @return array
     *
     * This method specifies the validation rules for resource mutations, including create, update, attach, or detach.
     * It includes rules for the operation type, attributes, keys, and custom rules.
     */
    public function mutateRules(Resource $resource, $prefix = 'mutate.*', $loadedRelations = [])
    {
        return array_merge(
            [
                $prefix . '.operation' => [
                    'required_with:' . $prefix,
                    Rule::in('create', 'update', ...($prefix === '' ? [] : ['attach', 'detach'])),
                ],
                $prefix . '.attributes' => [
                    'prohibited_if:' . $prefix . '.operation,attach',
                    'prohibited_if:' . $prefix . '.operation,detach',
                    new ArrayWith($resource->getFields($this)),
                ],
                $prefix . '.key' => [
                    'required_if:' . $prefix . '.operation,update',
                    'required_if:' . $prefix . '.operation,attach',
                    'required_if:' . $prefix . '.operation,detach',
                    'prohibited_if:' . $prefix . '.operation,create',
                    'exists:' . $resource::newModel()->getTable() . ',' . $resource::newModel()->getKeyName(),
                ],
                $prefix => [
                    CustomRulable::make()->resource($resource),
                ],
            ],
            $this->relationRules($resource, $prefix . '.relations', $loadedRelations)
        );
    }

    /**
     * Define relation-specific validation rules for mutations.
     *
     * @param array $loadedRelations
     *
     * @return array
     *
     * This protected method specifies validation rules for resource relations during mutations.
     * It ensures that relations are properly validated for the given operation type.
     */
    protected function relationRules(Resource $resource, string $prefix = '', $loadedRelations = [])
    {
        $resourceRelationsNotLoaded = collect($resource->getRelations($this))
            ->filter(fn ($relation) => !in_array($relation->relation, $loadedRelations));

        $rules = [
            $prefix => [
                new ArrayWith(
                    $resourceRelationsNotLoaded->map(fn ($resourceRelationNotLoaded) => $resourceRelationNotLoaded->relation)->toArray()
                ),
            ],
        ];

        foreach (
            $resourceRelationsNotLoaded as $relation
        ) {
            $prefixRelation = $prefix . '.' . $relation->relation;

            if ($relation->hasMultipleEntries()) {
                $prefixRelation .= '.*';
            }

            $rules = array_merge_recursive(
                $rules,
                $relation->rules($resource, $prefix . '.' . $relation->relation),
                $this->mutateRules($relation->resource(), $prefixRelation, array_merge($loadedRelations, [$relation->relation]))
            );
        }

        return $rules;
    }
}
