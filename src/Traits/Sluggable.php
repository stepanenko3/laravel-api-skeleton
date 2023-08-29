<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait Sluggable
{
    public function scopeFindBySlug(Builder $query, string $slug): Model
    {
        return $query->where($this->getSlugFieldName(), $slug)->firstOrFail();
    }

    public function generateSlug(): void
    {
        $this->{$this->getSlugFieldName()} = Str::slug($this->{$this->getSlugSourceName()});
    }

    protected static function bootSluggable(): void
    {
        static::creating(function (Model $model): void {
            if (
                $model->enableSluggableInCreating()
                && ($this->enableSlugReplace() || empty($model->{$this->getSlugFieldName()} ?? null))
            ) {
                $model->generateSlug();
            }
        });

        static::updating(function (Model $model): void {
            if (
                $model->enableSluggableInUpdating()
                && ($this->enableSlugReplace() || empty($model->{$this->getSlugFieldName()} ?? null))
            ) {
                $model->generateSlug();
            }
        });
    }

    protected function enableSluggableInCreating(): bool
    {
        return true;
    }

    protected function enableSluggableInUpdating(): bool
    {
        return true;
    }

    protected function enableSlugReplace(): bool
    {
        return false;
    }

    protected function getSlugFieldName(): string
    {
        return 'slug';
    }

    protected function getSlugSourceName(): string
    {
        return 'name';
    }
}
