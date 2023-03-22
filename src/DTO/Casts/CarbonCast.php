<?php

namespace Stepanenko3\LaravelApiSkeleton\DTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Interfaces\DtoCastInterface;
use Carbon\Carbon;
use Throwable;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastException;

class CarbonCast implements DtoCastInterface
{
    public function __construct(
        private readonly ?string $timezone = null,
        private readonly ?string $format = null,
    ) {
        //
    }

    public function cast(
        string $property,
        mixed $value,
    ): Carbon {
        try {
            return null === $this->format
                ? Carbon::parse($value, $this->timezone)
                : Carbon::createFromFormat($this->format, $value, $this->timezone);
        } catch (Throwable) {
            throw new CastException($property);
        }
    }
}
