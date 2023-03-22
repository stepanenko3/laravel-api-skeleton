<?php

namespace Stepanenko3\LaravelApiSkeleton\DTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Interfaces\DtoCastInterface;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastException;

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
