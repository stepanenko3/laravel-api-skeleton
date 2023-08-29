<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Throwable;

class UnAuthenticatedResponse extends ErrorResponse implements Responsable
{
    public function __construct(
        private readonly string $message = 'You are not authenticated to preform this actions',
        private readonly array $data = [],
        private readonly ?Throwable $exception = null,
        private readonly int $code = Response::HTTP_UNAUTHORIZED,
        private readonly array $headers = [],
    ) {
        //
    }
}
