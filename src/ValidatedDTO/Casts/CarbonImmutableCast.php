<?php

namespace Stepanenko3\LaravelApiSkeleton\ValidatedDTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Contracts\ValidatedDtoCastContract;
use Carbon\CarbonImmutable;
use Throwable;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastException;

class CarbonImmutableCast implements ValidatedDtoCastContract
{
    public function __construct(
        private readonly ?string $timezone = null,
        private readonly ?string $format = null,
    ) {
        //
    }

    public function cast(string $property, mixed $value): CarbonImmutable
    {
        try {
            return null === $this->format
                ? CarbonImmutable::parse($value, $this->timezone)
                : CarbonImmutable::createFromFormat($this->format, $value, $this->timezone);
        } catch (Throwable) {
            throw new CastException($property);
        }
    }
}
