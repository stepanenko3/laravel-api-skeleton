<?php

namespace Stepanenko3\LaravelLogicContainers\Rules;

use Closure;
use Illuminate\Contracts\Validation\InvokableRule;

class PhonesJson implements InvokableRule
{
    public function __invoke(string $attribute, mixed $value, Closure $fail): void
    {
        $json = json_decode($value, 1);

        if (!$json || !is_array($json)) {
            $fail(trans('The validation error message.'));
        }

        foreach ($json as $phone) {
            try {
                $proto = phone($phone, ['INTERNATIONAL']);

                if (!$proto || !(string) $proto->formatE164()) {
                    $fail(trans('The validation error message.'));
                }
            } catch (\Exception $e) {
                $fail(trans('The validation error message.'));
            }
        }
    }
}
