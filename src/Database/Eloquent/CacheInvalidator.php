<?php

namespace Stepanenko3\LaravelApiSkeleton\Database\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

class CacheInvalidator
{
    public static function invalidate(
        Model $model,
    ): void {
        $tags = self::getCacheTags(
            model: $model,
        );

        $key = self::getCacheKey(
            model: $model,
        );

        Cache::forget($key);
        Cache::tags($tags)
            ->flush();
    }

    public static function getCacheTags(
        Model $model,
    ): array {
        return [
            $model::class,
            $model->getTable(),
        ];
    }

    public static function getCacheKey(
        Model $model,
    ): string {
        return $model->getTable() . ':' . $model->getKey();
    }

    public static function registerEvents(): void
    {
        Event::listen(
            events: 'eloquent.created: *',
            listener: function (
                string $event,
                array $models,
            ): void {
                foreach ($models as $model) {
                    if ($model instanceof Model) {
                        self::invalidate($model);
                    }
                }
            }
        );

        Event::listen(
            events: 'eloquent.updated: *',
            listener: function (
                string $event,
                array $models,
            ): void {
                foreach ($models as $model) {
                    if ($model instanceof Model) {
                        self::invalidate($model);
                    }
                }
            }
        );

        Event::listen(
            events: 'eloquent.deleted: *',
            listener: function (
                string $event,
                array $models,
            ): void {
                foreach ($models as $model) {
                    if ($model instanceof Model) {
                        self::invalidate($model);
                    }
                }
            }
        );
    }
}
