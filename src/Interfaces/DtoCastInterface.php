<?php

namespace Stepanenko3\LaravelLogicContainers\Interfaces;

interface DtoCastInterface
{
    public function cast(
        string $property,
        mixed $value,
    ): mixed;
}
