<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class KeysIn implements ValidationRule
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
        if (is_array($value)) {
            $allowedKeys = array_flip(
                array: $this->values,
            );

            $unknownKeys = array_diff_key($value, $allowedKeys);

            if (count($unknownKeys) !== 0) {
                $fail('The selected :attribute must be in: :list')->translate(
                    replace: [
                        'list' => implode(
                            separator: ', ',
                            array: $this->values,
                        ),
                    ]
                );
            }
        }
    }
}
