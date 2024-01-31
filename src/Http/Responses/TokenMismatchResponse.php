<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Throwable;

class TokenMismatchResponse extends ErrorResponse implements Responsable
{
    public function __construct(
        protected readonly string $message = 'CSRF token mismatch',
        protected readonly array $data = [],
        protected readonly ?Throwable $exception = null,
        protected readonly int $code = 419,
        protected readonly array $headers = [],
    ) {
        //
    }
}
