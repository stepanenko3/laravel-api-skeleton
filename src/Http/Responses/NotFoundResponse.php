<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Throwable;

class NotFoundResponse extends ErrorResponse implements Responsable
{
    public function __construct(
        protected readonly string $message = 'Not Found',
        protected readonly array $data = [],
        protected readonly ?Throwable $exception = null,
        protected readonly int $code = Response::HTTP_NOT_FOUND,
        protected readonly array $headers = [],
    ) {
        //
    }
}
