<?php

namespace Stepanenko3\LaravelApiSkeleton\ValidatedDTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Contracts\ValidatedDtoCastContract;

class BooleanCast implements ValidatedDtoCastContract
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
