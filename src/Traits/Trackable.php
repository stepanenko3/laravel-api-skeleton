<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

trait Trackable
{
    public static function findOrCreateCached(
        array $attributes,
        ?array $keys = null,
        bool &$created = false,
    ) {
        $model = static::query();

        $keys = $keys ?: array_keys($attributes);

        foreach ($keys as $key) {
            $model = $model->where(
                column: $key,
                operator: '=',
                value: $attributes[$key],
            );
        }

        if (!$model = $model->first()) {
            $model = static::query()
                ->create($attributes);

            $created = true;
        }

        return $model;
    }

    public static function findCached(
        array $attributes,
    ) {
        $model = static::query();

        $keys = array_keys($attributes);

        foreach ($keys as $key) {
            $model = $model->where(
                column: $key,
                operator: '=',
                value: $attributes[$key],
            );
        }

        return $model->first();
    }
}
