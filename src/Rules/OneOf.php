<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OneOf implements ValidationRule
{
    public function __construct(
        private array $oneOf,
    ) {
        //
    }

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        $keys = array_filter(
            array: array_keys($value),
            callback: fn ($key) => in_array(
                needle: $key,
                haystack: $this->oneOf,
            ),
        );

        if (count($keys) === 0) {
            $fail(trans(':attribute require one field of ' . implode(', ', $this->oneOf)));
        }

        if (count($keys) > 1) {
            $fail(trans(':attribute allow only one field of ' . implode(', ', $this->oneOf)));
        }
    }
}
