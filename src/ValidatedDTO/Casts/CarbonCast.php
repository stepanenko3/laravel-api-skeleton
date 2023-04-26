<?php

namespace Stepanenko3\LaravelApiSkeleton\ValidatedDTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Contracts\ValidatedDtoCastContract;
use Carbon\Carbon;
use Throwable;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastException;

class CarbonCast implements ValidatedDtoCastContract
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
