<?php

namespace Stepanenko3\LaravelLogicContainers\DTO\Casts;

use Stepanenko3\LaravelLogicContainers\Interfaces\DtoCastInterface;
use Carbon\CarbonImmutable;
use Throwable;
use Stepanenko3\LaravelLogicContainers\Exceptions\DTO\CastException;

class CarbonImmutableCast implements DtoCastInterface
{
    public function __construct(
        private ?string $timezone = null,
        private ?string $format = null,
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
