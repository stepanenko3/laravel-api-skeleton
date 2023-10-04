<?php

namespace Stepanenko3\LaravelApiSkeleton\Contracts;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;

interface RelationSchema
{
    public function filter(
        Builder $query,
        mixed $relation,
        mixed $operator,
        mixed $value,
        string $boolean = 'and',
        Closure | null $callback = null,
    ): Builder;
}
