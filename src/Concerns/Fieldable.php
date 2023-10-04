<?php

namespace Stepanenko3\LaravelApiSkeleton\Concerns;

use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;

trait Fieldable
{
    /**
     * The fields.
     */
    public function fields(Request $request): array
    {
        return [];
    }

    public function field(
        Request $request,
        string $name,
    ): array {
        return collect($this->fields($request))
            ->first(fn ($value, $fieldName) => $fieldName === $name);
    }
}
