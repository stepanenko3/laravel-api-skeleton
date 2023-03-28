<?php

namespace Stepanenko3\LaravelApiSkeleton\Processes;

use Illuminate\Support\Facades\Pipeline;

abstract class Process
{
    public array $tasks;

    public function handle(
        object $payload,
    ): mixed {
        return Pipeline::send(
            passable: $payload,
        )->through(
            pipes: $this->tasks,
        )->thenReturn();
    }
}
