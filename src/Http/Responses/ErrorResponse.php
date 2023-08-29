<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class ErrorResponse implements Responsable
{
    public function __construct(
        protected readonly string $message = 'Something went wrong',
        protected readonly array $data = [],
        protected readonly ?Throwable $exception = null,
        protected readonly int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        protected readonly array $headers = [],
    ) {
        //
    }

    public function toResponse($request): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $this->message,
            'data' => $this->data,
        ];

        if (null !== $this->exception && config('app.debug')) {
            $response['debug'] = [
                'message' => $this->exception->getMessage(),
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
                'trace' => $this->exception->getTraceAsString(),
            ];
        }

        return response()->json(
            data: $response,
            status: $this->code,
            headers: array_merge(
                [
                    'Content-Type' => 'application/problem+json',
                ],
                $this->headers,
            ),
        );
    }
}
