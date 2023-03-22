<?php

namespace Stepanenko3\LaravelApiSkeleton\DTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Interfaces\DtoCastInterface;
use Throwable;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastException;

class StringCast implements DtoCastInterface
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
