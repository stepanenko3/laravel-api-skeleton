<?php

namespace Stepanenko3\LaravelLogicContainers\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class ErrorResponse implements Responsable
{
    public function __construct(
        private readonly string $message = 'Something went wrong',
        private readonly array $data = [],
        private readonly ?Throwable $exception = null,
        private readonly int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        private readonly array $headers = [],
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
            $response,
            $this->code,
            $this->headers,
        );
    }
}
