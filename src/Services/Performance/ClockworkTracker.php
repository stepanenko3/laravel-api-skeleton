<?php

namespace Stepanenko3\LaravelApiSkeleton\Services\Performance;

use Stepanenko3\LaravelApiSkeleton\Contracts\PerformanceTrackerContract;
use Clockwork\Request\Timeline\Event;

class ClockworkTracker implements PerformanceTrackerContract
{
    /** @var Event[] */
    protected array $activeEvents = [];

    public function begin(
        string $name,
        array $params = [],
    ): void {
        if (!function_exists('clock')) {
            return;
        }

        $clockwork = clock();

        if (!$clockwork) {
            return;
        }

        $event = $clockwork->event($name);

        if ($params['color'] ?? null) {
            $event->color($params['color']);
        }

        if ($params['description'] ?? null) {
            $event->description($params['description']);
        }

        if ($params['duration'] ?? null) {
            $event->duration($params['duration']);
        }

        $event->begin();

        $this->activeEvents[$name] = $event;
    }

    public function end(
        string $name,
    ): void {
        if (isset($this->activeEvents[$name])) {
            $this->activeEvents[$name]->end();

            unset($this->activeEvents[$name]);
        }
    }
}
