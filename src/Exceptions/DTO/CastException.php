<?php

namespace Stepanenko3\LaravelApiSkeleton\Exceptions\DTO;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class CastException extends Exception
{
    public function __construct(
        string $property,
    ) {
        parent::__construct(
            message: "Unable to cast property: {$property} - invalid value",
            code: Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }
}
