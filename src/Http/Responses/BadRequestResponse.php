<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Throwable;

class BadRequestResponse extends ErrorResponse implements Responsable
{
    public function __construct(
        private readonly string $message = 'Bad request',
        private readonly array $data = [],
        private readonly ?Throwable $exception = null,
        private readonly int $code = Response::HTTP_BAD_REQUEST,
        private readonly array $headers = [],
    ) {
        //
    }
}
