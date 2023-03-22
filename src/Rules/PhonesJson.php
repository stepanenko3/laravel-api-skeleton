<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\InvokableRule;

class PhonesJson implements InvokableRule
{
    public function __invoke(string $attribute, mixed $value, Closure $fail): void
    {
        $json = json_decode((string) $value, 1, 512, JSON_THROW_ON_ERROR);

        if (!$json || !is_array($json)) {
            $fail(trans('The validation error message.'));
        }

        foreach ($json as $phone) {
            try {
                $proto = phone($phone, ['INTERNATIONAL']);

                if (!$proto || !(string) $proto->formatE164()) {
                    $fail(trans('The validation error message.'));
                }
            } catch (\Exception) {
                $fail(trans('The validation error message.'));
            }
        }
    }
}
