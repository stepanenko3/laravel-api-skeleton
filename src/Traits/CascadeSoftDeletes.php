<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;
use Stepanenko3\LaravelApiSkeleton\Exceptions\CascadeSoftDeleteException;

trait CascadeSoftDeletes
{
    // Add cascadeDeletes property to your model
    // protected $cascadeDeletes = ['relation_name'];

    // Remove the model with cascading deletes
    // $model->delete();

    protected static function bootCascadeSoftDeletes()
    {
        static::deleting(function ($model) {
            $model->validateCascadingSoftDelete();

            $model->runCascadingDeletes();
        });
    }

    protected function validateCascadingSoftDelete(): void
    {
        if (!$this->implementsSoftDeletes()) {
            throw CascadeSoftDeleteException::softDeleteNotImplemented(
                class: get_called_class(),
            );
        }

        if ($invalidCascadingRelationships = $this->hasInvalidCascadingRelationships()) {
            throw CascadeSoftDeleteException::invalidRelationships(
                relationships: $invalidCascadingRelationships,
            );
        }
    }

    protected function runCascadingDeletes(): void
    {
        foreach ($this->getActiveCascadingDeletes() as $relationship) {
            $this->cascadeSoftDeletes(
                relationship: $relationship,
            );
        }
    }


    protected function cascadeSoftDeletes(
        $relationship,
    ): void {
        $delete = $this->forceDeleting ? 'forceDelete' : 'delete';

        foreach ($this->{$relationship}()->get() as $model) {
            isset($model->pivot) ? $model->pivot->{$delete}() : $model->{$delete}();
        }
    }

    protected function implementsSoftDeletes(): bool
    {
        return method_exists($this, 'runSoftDelete');
    }

    protected function hasInvalidCascadingRelationships(): array
    {
        return array_filter(
            array: $this->getCascadingDeletes(),
            callback: fn ($relationship) =>  !method_exists($this, $relationship) ||
                !$this->{$relationship}() instanceof Relation,
        );
    }


    protected function getCascadingDeletes(): array
    {
        return isset($this->cascadeDeletes)
            ? (array) $this->cascadeDeletes
            : [];
    }


    protected function getActiveCascadingDeletes(): array
    {
        return array_filter(
            array: $this->getCascadingDeletes(),
            callback: fn ($relationship) => $this->{$relationship}()->exists(),
        );
    }
}
