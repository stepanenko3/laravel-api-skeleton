<?php

namespace Stepanenko3\LaravelApiSkeleton\Contracts;

use Illuminate\Http\Request;

interface DtoContract
{
    public function toArray(): array;

    public static function fromRequest(Request $request): static;

    public static function fromArray(array $data): static;
}
