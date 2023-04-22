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
        return $this->can(
            subject: $subject,
            key: 'viewAny',
        );
    }

    public function view(?Model $subject, Model $item)
    {
        return $this->can(
            subject: $subject,
            key: 'view',
        );
    }

    public function create(Model $subject)
    {
        return $this->can(
            subject: $subject,
            key: 'create',
        );
    }

    public function update(Model $subject, Model $item)
    {
        return $this->can(
            subject: $subject,
            key: 'update',
        );
    }

    public function replicate(Model $subject, Model $item)
    {
        return $this->can(
            subject: $subject,
            key: 'replicate',
        );
    }

    public function delete(Model $subject, Model $item)
    {
        return $this->can(
            subject: $subject,
            key: 'create',
        );
    }

    public function restore(Model $subject, Model $item)
    {
        return $this->can(
            subject: $subject,
            key: 'restore',
        );
    }

    public function forceDelete(Model $subject, Model $item)
    {
        return $this->can(
            subject: $subject,
            key: 'forceDelete',
        );
    }

    protected function can(?Model $subject, string $key): bool
    {
        if ($subject === null) {
            return false;
        }

        if ($subject->can($this->getKey(suffix: $key))) {
            return true;
        }

        return false;
    }

    protected function getKey(string $suffix = ''): string
    {
        return $this->key() . ($suffix ? '.' . $suffix : '');
    }

    public function runAction(Model $subject, Model $item)
    {
        return $this->can(
            subject: $subject,
            key: 'runAction',
        );
    }

    public function runDestructiveAction(Model $subject, Model $item)
    {
        return $this->can(
            subject: $subject,
            key: 'runDestructiveAction',
        );
    }
}
