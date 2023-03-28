<?php

namespace Stepanenko3\LaravelApiSkeleton\Interfaces;

interface ActionInterface
{
    public function handle(object $dto): mixed;
}
