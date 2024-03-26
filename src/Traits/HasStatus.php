<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Database\Query\Builder;

trait HasStatus
{
    public static function statusField(): string
    {
        return 'status';
    }

    public function scopeActive(
        Builder $query,
    ): Builder {
        return $query->where(
            column: self::statusField(),
            operator: '=',
            value: 1,
        );
    }

    public function scopeDisabled(
        Builder $query,
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
