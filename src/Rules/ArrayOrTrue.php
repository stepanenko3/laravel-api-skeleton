<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class ArrayOrTrue implements InvokableRule
{
    public function __invoke($attribute, $value, $fail): void
    {
        if ((!is_array($value) || (is_array($value) && empty($value))) && $value !== true) {
            $fail(trans(':attribute contains invalid fields'));
        }
    }
}
