<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhonesJson implements ValidationRule
{
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        $json = json_decode((string) $value, 1, 512, JSON_THROW_ON_ERROR);

        if (!$json || !is_array($json)) {
            $fail(trans('The validation error message.'));
        }

        foreach ($json as $phone) {
            try {
                $proto = phone(
                    number: $phone,
                    country: ['INTERNATIONAL'],
                );

                if (!$proto || !(string) $proto->formatE164()) {
                    $fail(trans('The validation error message.'));
                }
            } catch (\Exception) {
                $fail(trans('The validation error message.'));
            }
        }
    }
}
