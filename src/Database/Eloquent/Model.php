<?php

namespace Stepanenko3\LaravelApiSkeleton\Database\Eloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    public function newEloquentBuilder($query)
    {
        return new Builder(
            query: $query,
        );
    }
}
