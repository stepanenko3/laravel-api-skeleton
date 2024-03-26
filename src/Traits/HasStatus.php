<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilderContract;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder;

trait HasStatus
{
    public static function statusField(): string
    {
        return 'status';
    }

    public function scopeActive(
        EloquentBuilderContract | QueryBuilderContract | Builder $query,
    ): Builder {
        return $query->where(
            column: self::statusField(),
            operator: '=',
            value: 1,
        );
    }

    public function scopeDisabled(
        EloquentBuilderContract | QueryBuilderContract | Builder $query,
    ): Builder {
        return $query->where(
            column: self::statusField(),
            operator: '=',
            value: 0,
        );
    }

    public function toggleActivation(): void
    {
        $this->{self::statusField()} = !$this->{self::statusField()};

        $this->save();
    }
}
