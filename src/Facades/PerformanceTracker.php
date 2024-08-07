<?php

namespace Stepanenko3\LaravelApiSkeleton\Facades;

use Illuminate\Support\Facades\Facade;
use Stepanenko3\LaravelApiSkeleton\Services\Performance\PerformanceTracker as PerformancePerformanceTracker;

class PerformanceTracker extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PerformancePerformanceTracker::class;
    }
}
