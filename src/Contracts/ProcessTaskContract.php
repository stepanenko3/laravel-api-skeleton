<?php

namespace Stepanenko3\LaravelApiSkeleton\Contracts;

use Closure;

interface ProcessTaskContract
{
    public function __invoke(DtoContract $payload, Closure $next): mixed;
}
