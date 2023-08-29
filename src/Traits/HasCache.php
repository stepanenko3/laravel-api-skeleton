<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Stepanenko3\LaravelApiSkeleton\Enum\CacheKeys;

trait HasCache
{
    public function flushCache(): void
    {
        Cache::forget(
            key: $this->cacheKey(),
        );
    }

    protected static function bootHasCache(): void
    {
        static::updated(function (Model $model): void {
            $model->flushCache();
        });

        static::created(function (Model $model): void {
            $model->flushCache();
        });
    }

    protected function cacheKey(): string
    {
        return CacheKeys::DEFAULT_CACHE_KEY;
    }
}
