<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\InvokableRule;

class KeysIn implements InvokableRule
{
    public function __construct(
        protected array $values,
    ) {
    }

    public function __invoke(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_array($value)) {
            $allowedKeys = array_flip($this->values);

            $unknownKeys = array_diff_key($value, $allowedKeys);

            if (count($unknownKeys) !== 0) {
                $fail(trans(':attribute contains invalid fields'));
            }
        }
    }
}
