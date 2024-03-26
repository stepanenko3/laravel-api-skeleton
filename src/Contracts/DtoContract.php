<?php

namespace Stepanenko3\LaravelApiSkeleton\Contracts;

use Illuminate\Http\Request;

interface DtoContract
{
    public static function fromRequest(
        Request $request,
    ): static;

    public static function fromArray(
        array $data,
    ): static;

    public function toArray(): array;
}
