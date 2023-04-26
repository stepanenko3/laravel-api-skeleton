<?php

namespace Stepanenko3\LaravelApiSkeleton\ValidatedDTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastException;
use Stepanenko3\LaravelApiSkeleton\Contracts\ValidatedDtoCastContract;

class FloatCast implements ValidatedDtoCastContract
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
