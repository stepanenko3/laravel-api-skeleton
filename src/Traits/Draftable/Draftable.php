<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits\Draftable;

use Stepanenko3\LaravelApiSkeleton\Scopes\DraftableScope;

/** @phpstan-consistent-constructor */
trait Draftable
{
    public static function bootDraftable(): void
    {
        static::addGlobalScope(new DraftableScope());
    }

    public static function published($id)
    {
        return (new static())
            ->newQueryWithoutScope(new DraftableScope())
            ->markAsPublished($id);
    }

    public static function draft($id)
    {
        return (new static())
            ->newQueryWithoutScope(new DraftableScope())
            ->markAsDraft($id);
    }

    public static function onlyPublished()
    {
        return (new static())
            ->newQueryWithoutScope(new DraftableScope())
            ->onlyPublished();
    }

    public static function onlyDrafts()
    {
        return (new static())
            ->newQueryWithoutScope(new DraftableScope())
            ->onlyDrafts();
    }

    public static function withPublished()
    {
        return (new static())
            ->newQueryWithoutScope(new DraftableScope())
            ->withPublished();
    }

    public static function withDrafts()
    {
        return (new static())
            ->newQueryWithoutScope(new DraftableScope())
            ->withDrafts();
    }

    public static function withAnyDraftStatus()
    {
        return (new static())
            ->newQueryWithoutScope(new DraftableScope());
    }

    public function scopeAnyStatus($q)
    {
        return $q->withAnyDraftStatus();
    }

    public function markAsPublished()
    {
        $new = (new static())
            ->newQueryWithoutScope(new DraftableScope())
            ->markAsPublished($this->id);

        return $this->setRawAttributes($new->attributesToArray());
    }

    public function markAsDraft()
    {
        $new = (new static())
            ->newQueryWithoutScope(new DraftableScope())
            ->markAsDraft($this->id);

        return $this->setRawAttributes($new->attributesToArray());
    }

    public function isPublished()
    {
        return $this->{$this->draftStatusColumn()} == DraftStatus::PUBLISHED;
    }

    public function isDraft()
    {
        return $this->{$this->draftStatusColumn()} == DraftStatus::DRAFT;
    }

    public function draftStatusColumn()
    {
        return $this->draft_status_column ?? 'status';
    }

    public function getQualifiedDraftStatusColumn()
    {
        return $this->qualifyColumn($this->draftStatusColumn());
    }
}
