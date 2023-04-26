<?php

namespace Stepanenko3\LaravelApiSkeleton\Database\Eloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;

abstract class Model extends BaseModel
{
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    public function scopeApplySchema(
        QueryBuilder | EloquentBuilder $builder,
        string $schema,
        array $fields,
        array $with,
        array $withCount,
    ): QueryBuilder | EloquentBuilder {
        return $schema::applyToQuery(
            builder: $builder,
            fields: $fields,
            with: $with,
            withCount: $withCount,
        );
    }

    public function scopeOnly(
        QueryBuilder | EloquentBuilder $q,
        ?array $values,
        string $field = 'id',
    ): QueryBuilder | EloquentBuilder {
        return $q->when(
            $values,
            fn ($query) => $query->whereIn(
                $this->qualifyColumn($field),
                $values,
            ),
        );
    }

    public function scopeExclude(
        QueryBuilder | EloquentBuilder $q,
        ?array $values,
        string $field = 'id',
    ): QueryBuilder | EloquentBuilder {
        return $q->when(
            $values,
            fn ($query) => $query->whereNotIn(
                $this->qualifyColumn($field),
                $values,
            ),
        );
    }
}
