<?php

namespace Stepanenko3\LaravelApiSkeleton\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

        if ($subject->can($this->getKey('edit'))) {
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

        if ($subject->can($this->getKey('force-delete'))) {
            return true;
        }
    }

    protected function getKey(string $suffix = ''): string
    {
        return Str::plural($this->key()) . ($suffix ? '.' . $suffix : '');
    }
}
