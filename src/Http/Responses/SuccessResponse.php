<?php

namespace Stepanenko3\LaravelLogicContainers\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Stepanenko3\LaravelLogicContainers\Helpers\ApiPagination;

class SuccessResponse implements Responsable
{
    public function __construct(
        private mixed $data,
        private array $metadata = [],
        private int $code = Response::HTTP_OK,
        private array $headers = [],
        private mixed $pagination = null,
    ) {
        if ($this->pagination) {
            $this->metadata['pagination'] = (new ApiPagination($this->pagination))->toArray();
        }
    }

    public function toResponse($request): JsonResponse
    {
        return response()->json(
            [
                'success' => true,
                'data' => $this->data,
                'metadata' => $this->metadata,
            ],
            $this->code,
            $this->headers,
        );
    }
}
