<?php

namespace Stepanenko3\LaravelApiSkeleton;

use Illuminate\Http\Request;
use Stepanenko3\LaravelApiSkeleton\Contracts\DtoContract;

abstract class DTO implements DtoContract
{
    public static function fromRequest(Request $request): static
    {
        return new static(
            ...$request->validated(),
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            ...$data,
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
