<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Closure;

class UniqueGroupKeys implements ValidationRule
{

    public function __construct(
        protected array $groups,
    ) {
        //
    }

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        // Extract the keys from the $value array
        $keys = array_keys($value);

        foreach ($this->groups as $group) {
            $intersect = array_intersect($keys, $group);

            if (count($intersect) > 1) {
                $fail("The {$attribute} has more than one key from the same group.");
            }
        }
    }
}
