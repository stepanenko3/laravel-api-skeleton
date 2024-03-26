<?php

namespace Stepanenko3\LaravelApiSkeleton\Exceptions\DTO;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class CastTargetException extends Exception
{
    public function __construct(
        string $property,
    ) {
        parent::__construct(
            message: "The property: {$property} has an invalid cast configuration",
            code: Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }
}
