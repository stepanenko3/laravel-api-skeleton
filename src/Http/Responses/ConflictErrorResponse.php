<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Throwable;

class ConflictErrorResponse extends ErrorResponse implements Responsable
{
    public function __construct(
        protected readonly string $message = 'Conflict',
        protected readonly array $data = [],
        protected readonly ?Throwable $exception = null,
        protected readonly int $code = Response::HTTP_CONFLICT,
        protected readonly array $headers = [],
    ) {
        //
    }
}
