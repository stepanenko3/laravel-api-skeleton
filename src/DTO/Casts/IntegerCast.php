<?php

namespace Stepanenko3\LaravelLogicContainers\DTO\Casts;

use Stepanenko3\LaravelLogicContainers\Interfaces\DtoCastInterface;
use Stepanenko3\LaravelLogicContainers\Exceptions\DTO\CastException;

class IntegerCast implements DtoCastInterface
{
    public function cast(
        string $property,
        mixed $value,
    ): int {
        if (!is_numeric($value)) {
            throw new CastException($property);
        }

        return (int) $value;
    }
}
