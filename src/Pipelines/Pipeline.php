<?php

namespace Stepanenko3\LaravelApiSkeleton\Pipelines;

use Illuminate\Support\Facades\Pipeline as IlluminatePipeline;

abstract class Pipeline
{
    public array $tasks;

    public function handle(
        object $payload,
    ): mixed {
        return IlluminatePipeline::send(
            passable: $payload,
        )->through(
            pipes: $this->tasks,
        )->thenReturn();
    }
}
