<?php

namespace Stepanenko3\LaravelApiSkeleton\ValidatedDTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Contracts\ValidatedDtoCastContract;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastException;

class IntegerCast implements ValidatedDtoCastContract
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
