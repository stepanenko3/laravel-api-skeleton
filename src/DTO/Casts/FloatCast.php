<?php

namespace Stepanenko3\LaravelLogicContainers\DTO\Casts;

use Stepanenko3\LaravelLogicContainers\Exceptions\DTO\CastException;
use Stepanenko3\LaravelLogicContainers\Interfaces\DtoCastInterface;

class FloatCast implements DtoCastInterface
{
    public function cast(
        string $property,
        mixed $value,
    ): float {
        if (!is_numeric($value)) {
            throw new CastException($property);
        }

        return (float) $value;
    }
}
