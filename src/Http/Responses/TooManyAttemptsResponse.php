<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Throwable;

class TooManyAttemptsResponse extends ErrorResponse implements Responsable
{
    public function __construct(
        private readonly string $message = 'Too Many Attempts.',
        private readonly array $data = [],
        private readonly ?Throwable $exception = null,
        private readonly int $code = Response::HTTP_TOO_MANY_REQUESTS,
        private readonly array $headers = [],
    ) {
        //
    }
}
