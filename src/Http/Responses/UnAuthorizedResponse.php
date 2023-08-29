<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Throwable;

class UnAuthorizedResponse extends ErrorResponse implements Responsable
{
    public function __construct(
        protected readonly string $message = 'You are not authorized to preform this actions',
        protected readonly array $data = [],
        protected readonly ?Throwable $exception = null,
        protected readonly int $code = Response::HTTP_FORBIDDEN,
        protected readonly array $headers = [],
    ) {
        //
    }
}
