<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Spatie\OpeningHours\OpeningHours as SpatieOpeningHours;

class OpeningHours implements ValidationRule
{
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        try {
            SpatieOpeningHours::create(
                data: $value,
            );
        } catch (\Exception) {
            $fail(trans('app.open_hours.error_overlap'));
        }
    }
}
