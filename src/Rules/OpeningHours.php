<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\InvokableRule;
use Spatie\OpeningHours\OpeningHours as SpatieOpeningHours;

class OpeningHours implements InvokableRule
{
    public function __invoke(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            SpatieOpeningHours::create($value);
        } catch (\Exception) {
            $fail(trans('app.open_hours.error_overlap'));
        }
    }
}
