<?php

namespace Stepanenko3\LaravelApiSkeleton\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

abstract class Policy
{
    use HandlesAuthorization;

    abstract public function key(): string;

    public function viewAny(Model $subject)
    {
        if ($subject === null) {
            return false;
        }

        if ($subject->can($this->getKey('view'))) {
            return true;
        }
    }

    public function view(?Model $subject, Model $item)
    {
        if ($subject === null) {
            return false;
        }

        if ($subject->can($this->getKey('view'))) {
            return true;
        }
    }

    public function create(Model $subject)
    {
        if ($subject === null) {
            return false;
        }

        if ($subject->can($this->getKey('create'))) {
            return true;
        }
    }

    public function update(Model $subject, Model $item)
    {
        if ($subject === null) {
            return false;
        }

        if ($subject->can($this->getKey('update'))) {
            return true;
        }
    }

    public function replicate(Model $subject, Model $item)
    {
        if ($subject === null) {
            return false;
        }

        if ($subject->can($this->getKey('replicate'))) {
            return true;
        }
    }

    public function delete(Model $subject, Model $item)
    {
        if ($subject === null) {
            return false;
        }

        if ($subject->can($this->getKey('delete'))) {
            return true;
        }
    }

    public function restore(Model $subject, Model $item)
    {
        if ($subject === null) {
            return false;
        }

        if ($subject->can($this->getKey('restore'))) {
            return true;
        }
    }

    public function forceDelete(Model $subject, Model $item)
    {
        if ($subject === null) {
            return false;
        }

        if ($subject->can($this->getKey('forceDelete'))) {
            return true;
        }
    }

    public function runAction(Model $subject, Model $item)
    {
        if ($subject === null) {
            return false;
        }

        if ($subject->can($this->getKey('runAction'))) {
            return true;
        }
    }

    public function runDestructiveAction(Model $subject, Model $item)
    {
        if ($subject === null) {
            return false;
        }

        if ($subject->can($this->getKey('runDestructiveAction'))) {
            return true;
        }
    }

    protected function getKey(string $suffix = ''): string
    {
        return $this->key() . ($suffix ? '.' . $suffix : '');
    }
}
