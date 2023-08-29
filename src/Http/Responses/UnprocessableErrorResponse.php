<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Throwable;

class UnprocessableErrorResponse extends ErrorResponse implements Responsable
{
    public function __construct(
        private readonly string $message = 'Unprocessable entity',
        private readonly array $data = [],
        private readonly ?Throwable $exception = null,
        private readonly int $code = Response::HTTP_UNPROCESSABLE_ENTITY,
        private readonly array $headers = [],
    ) {
        //
    }
}
