<?php

namespace Stepanenko3\LaravelApiSkeleton\ValidatedDTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Contracts\ValidatedDtoCastContract;

class ArrayCast implements ValidatedDtoCastContract
{
    public function cast(
        string $property,
        mixed $value,
    ): array {
        if (is_string($value)) {
            $jsonDecoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

            return is_array($jsonDecoded) ? $jsonDecoded : [$value];
        }

        return is_array($value) ? $value : [$value];
    }
}
