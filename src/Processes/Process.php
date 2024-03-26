<?php

namespace Stepanenko3\LaravelApiSkeleton\Processes;

use Illuminate\Support\Facades\Pipeline;

/** @phpstan-consistent-constructor */
abstract class Process
{
    public array $tasks;

    public function __construct()
    {
        //
    }

    public static function run(
        object $payload,
    ): mixed {
        return (new static())->handle(
            payload: $payload,
        );
    }

    public function handle(
        object $payload,
    ): mixed {
        return Pipeline::send(
            passable: $payload,
        )
            ->through(
                pipes: $this->tasks,
            )
            ->thenReturn();
    }
}
