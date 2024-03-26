<?php

namespace Stepanenko3\LaravelApiSkeleton\Exceptions\DTO;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class InvalidJsonException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            message: 'The JSON string provided is not valid',
            code: Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }
}
