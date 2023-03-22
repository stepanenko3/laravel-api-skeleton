<?php

namespace Stepanenko3\LaravelApiSkeleton\DTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Interfaces\DtoCastInterface;
use Illuminate\Validation\ValidationException;
use Throwable;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastException;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastTargetException;

class DTOCast implements DtoCastInterface
{
    public function __construct(
        private readonly string $dtoClass,
    ) {
        //
    }

    public function cast(
        string $property,
        mixed $value,
    ): DtoCastInterface {
        if (is_string($value)) {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        if (!is_array($value)) {
            throw new CastException($property);
        }

        try {
            $dto = new $this->dtoClass($value);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new CastException($property);
        }

        if (!$dto instanceof DtoCastInterface) {
            throw new CastTargetException($property);
        }

        return $dto;
    }
}
