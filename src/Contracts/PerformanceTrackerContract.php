<?php

namespace Stepanenko3\LaravelApiSkeleton\Contracts;

interface PerformanceTrackerContract
{
    public function begin(
        string $name,
        array $params = [],
    );

    public function end(
        string $name,
    );
}
