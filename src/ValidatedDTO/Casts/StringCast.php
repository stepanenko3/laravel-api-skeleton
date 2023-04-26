<?php

namespace Stepanenko3\LaravelApiSkeleton\ValidatedDTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Contracts\ValidatedDtoCastContract;
use Throwable;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastException;

class StringCast implements ValidatedDtoCastContract
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
