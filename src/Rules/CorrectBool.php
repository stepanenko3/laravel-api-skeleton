<?php

namespace Stepanenko3\LaravelLogicContainers\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class CorrectBool implements InvokableRule
{
    public function __invoke($attribute, $value, $fail): void
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null) {
            $fail(trans(':attribute contains invalid fields'));
        }
    }
}
