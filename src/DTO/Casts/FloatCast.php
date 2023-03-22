<?php

namespace Stepanenko3\LaravelApiSkeleton\DTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastException;
use Stepanenko3\LaravelApiSkeleton\Interfaces\DtoCastInterface;

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
