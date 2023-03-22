<?php

namespace Stepanenko3\LaravelApiSkeleton\DTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Interfaces\DtoCastInterface;

class ArrayCast implements DtoCastInterface
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
