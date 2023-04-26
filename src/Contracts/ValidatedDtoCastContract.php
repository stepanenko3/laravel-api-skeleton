<?php

namespace Stepanenko3\LaravelApiSkeleton\Contracts;

interface ValidatedDtoCastContract
{
    public function cast(
        string $property,
        mixed $value,
    ): mixed;
}
