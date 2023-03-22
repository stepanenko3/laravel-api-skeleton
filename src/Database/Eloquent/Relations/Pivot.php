<?php

namespace Stepanenko3\LaravelLogicContainers\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Relations\Pivot as BasePivot;
use Stepanenko3\LaravelLogicContainers\Database\Eloquent\Builder;

abstract class Pivot extends BasePivot
{
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }
}
