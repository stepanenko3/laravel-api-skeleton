<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasStatus
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(self::statusField(), 1);
    }

    public function scopeDisabled(Builder $query): Builder
    {
        return $query->where(self::statusField(), 0);
    }

    public function toggleActivation(): void
    {
        $this->{self::statusField()} = !$this->{self::statusField()};

        $this->save();
    }

    public static function statusField(): string
    {
        return 'status';
    }
}
