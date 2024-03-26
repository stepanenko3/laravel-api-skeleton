<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RequiredArrayValues implements ValidationRule
{
    public function __construct(
        protected array $values,
    ) {
        //
    }

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        $unknownKeys = array_diff(
            $this->values,
            $value,
        );

        if (count($unknownKeys) !== 0) {
            $fail(trans('Values \'' . implode(', ', $this->values) . '\' in attribute :attribute is required'));
        }
    }
}
