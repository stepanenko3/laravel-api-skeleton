<?php

namespace Stepanenko3\LaravelApiSkeleton\DTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Interfaces\DtoCastInterface;

class BooleanCast implements DtoCastInterface
{
    public function cast(
        string $property,
        mixed $value,
    ): bool {
        if (is_numeric($value)) {
            return $value > 0;
        }

        if (is_string($value)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return (bool) $value;
    }
}
