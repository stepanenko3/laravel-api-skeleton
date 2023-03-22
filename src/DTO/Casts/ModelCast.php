<?php

namespace Stepanenko3\LaravelLogicContainers\DTO\Casts;

use Stepanenko3\LaravelLogicContainers\Interfaces\DtoCastInterface;
use Illuminate\Database\Eloquent\Model;
use Throwable;
use Stepanenko3\LaravelLogicContainers\Exceptions\DTO\CastException;
use Stepanenko3\LaravelLogicContainers\Exceptions\DTO\CastTargetException;

class ModelCast implements DtoCastInterface
{
    public function __construct(
        private readonly string $modelClass,
    ) {
        //
    }

    public function cast(
        string $property,
        mixed $value,
    ): Model {
        if (is_string($value)) {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        if (!is_array($value)) {
            throw new CastException($property);
        }

        try {
            $model = new $this->modelClass($value);
        } catch (Throwable) {
            throw new CastTargetException($property);
        }

        if (!$model instanceof Model) {
            throw new CastTargetException($property);
        }

        return $model;
    }
}
