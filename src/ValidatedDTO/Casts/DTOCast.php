<?php

namespace Stepanenko3\LaravelApiSkeleton\ValidatedDTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Contracts\ValidatedDtoCastContract;
use Illuminate\Validation\ValidationException;
use Throwable;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastException;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastTargetException;

class DTOCast implements ValidatedDtoCastContract
{
    public function __construct(
        private readonly string $dtoClass,
    ) {
        //
    }

    public function cast(
        string $property,
        mixed $value,
    ): ValidatedDtoCastContract {
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

        if (!$dto instanceof ValidatedDtoCastContract) {
            throw new CastTargetException($property);
        }

        return $dto;
    }
}
