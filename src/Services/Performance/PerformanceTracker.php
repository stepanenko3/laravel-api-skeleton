<?php

namespace Stepanenko3\LaravelApiSkeleton\Services\Performance;

use Stepanenko3\LaravelApiSkeleton\Contracts\PerformanceTrackerContract;

class PerformanceTracker
{
    /** @var PerformanceTrackerContract[] */
    protected array $trackers = [];

    public function __construct(
        array $trackers,
    ) {
        $this->trackers = $trackers;
    }

    public function begin(
        string $name,
        array $params = [],
    ): self {
        foreach ($this->trackers as $tracker) {
            $tracker->begin(
                name: $name,
                params: $params,
            );
        }

        return $this;
    }

    public function end(
        string $name,
    ): self {
        foreach ($this->trackers as $tracker) {
            $tracker->end(
                name: $name,
            );
        }

        return $this;
    }

    public function measure(
        string $name,
        callable $callback,
        array $params = [],
    ): mixed {
        $this->begin(
            name: $name,
            params: $params,
        );

        try {
            $result = $callback();
        } finally {
            $this->end(
                name: $name,
            );
        }

        return $result;
    }
}
