<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Stepanenko3\LaravelApiSkeleton\Helpers\ApiPagination;

class SuccessResponse implements Responsable
{
    public function __construct(
        private readonly mixed $data = [],
        private mixed $meta = [],
        private readonly int $code = Response::HTTP_OK,
        private readonly array $headers = [],
        private readonly mixed $pagination = null,
    ) {
        if ($this->pagination) {
            data_set(
                target: $this->meta,
                key: 'pagination',
                value: (new ApiPagination($this->pagination))->toArray(),
            );
        }
    }

    public function toResponse($request): JsonResponse
    {
        $data = [
            'success' => true,
            'data' => $this->data,
            'meta' => $this->meta,
        ];

        return response()->json(
            array_filter($data, fn ($v) => !empty($v)),
            $this->code,
            $this->headers,
        );
    }
}
