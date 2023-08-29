<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Throwable;

class BadRequestResponse extends ErrorResponse implements Responsable
{
    public function __construct(
        protected readonly string $message = 'Bad request',
        protected readonly array $data = [],
        protected readonly ?Throwable $exception = null,
        protected readonly int $code = Response::HTTP_BAD_REQUEST,
        protected readonly array $headers = [],
    ) {
        //
    }
}
