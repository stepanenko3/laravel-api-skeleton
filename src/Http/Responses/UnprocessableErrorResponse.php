<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Throwable;

class UnprocessableErrorResponse extends ErrorResponse implements Responsable
{
    public function __construct(
        protected readonly string $message = 'Unprocessable entity',
        protected readonly array $data = [],
        protected readonly ?Throwable $exception = null,
        protected readonly int $code = Response::HTTP_UNPROCESSABLE_ENTITY,
        protected readonly array $headers = [],
    ) {
        //
    }
}
