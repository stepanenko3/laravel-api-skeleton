<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Throwable;

class ConflictErrorResponse extends ErrorResponse implements Responsable
{
    public function __construct(
        private readonly string $message = 'Conflict',
        private readonly array $data = [],
        private readonly ?Throwable $exception = null,
        private readonly int $code = Response::HTTP_CONFLICT,
        private readonly array $headers = [],
    ) {
        //
    }
}
