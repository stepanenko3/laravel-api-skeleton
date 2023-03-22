<?php

namespace Stepanenko3\LaravelApiSkeleton\Interfaces;

interface DtoCastInterface
{
    public function cast(
        string $property,
        mixed $value,
    ): mixed;
}
