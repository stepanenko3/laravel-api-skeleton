<?php

namespace Stepanenko3\LaravelApiSkeleton\Processes;

use Closure;

abstract class Task
{
    abstract public function handle(mixed $payload, Closure $next): mixed;
}
