<?php

namespace Stepanenko3\LaravelApiSkeleton\DTO;

use Illuminate\Http\Request;
use Illuminate\Support\Traits\Conditionable;
use Stepanenko3\LaravelApiSkeleton\Contracts\DtoContract;

abstract class DTO implements DtoContract
{
    use Conditionable;

    public static function fromRequest(
        Request $request,
    ): static {
        return new static(
            ...$request->validated(),
        );
    }

    public static function fromArray(
        array $data,
    ): static {
        return new static(
            ...$data,
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
