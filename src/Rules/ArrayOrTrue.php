<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ArrayOrTrue implements ValidationRule
{
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        if ((!is_array($value) || (is_array($value) && empty($value))) && $value !== true) {
            $fail(trans(':attribute contains invalid fields'));
        }
    }
}
