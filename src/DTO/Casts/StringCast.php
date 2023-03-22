<?php

namespace Stepanenko3\LaravelLogicContainers\DTO\Casts;

use Stepanenko3\LaravelLogicContainers\Interfaces\DtoCastInterface;
use Throwable;
use Stepanenko3\LaravelLogicContainers\Exceptions\DTO\CastException;

class StringCast implements DtoCastInterface
{
    public function cast(
        string $property,
        mixed $value,
    ): string {
        try {
            return (string) $value;
        } catch (Throwable) {
            throw new CastException($property);
        }
    }
}
