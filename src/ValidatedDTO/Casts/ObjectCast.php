<?php

namespace Stepanenko3\LaravelApiSkeleton\ValidatedDTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Contracts\ValidatedDtoCastContract;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastException;

class ObjectCast implements ValidatedDtoCastContract
{
    public function cast(
        string $property,
        mixed $value,
    ): object {
        if (is_string($value)) {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        if (!is_array($value)) {
            throw new CastException($property);
        }

        return (object) $value;
    }
}
