<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Exception;
use Spatie\OpeningHours\OpeningHours as OpeningHoursOpeningHours;

trait OpeningHours
{
    public function openingHours(
        string $key = 'opening_hours',
    ): ?OpeningHoursOpeningHours {
        try {
            if ($this->{$key} && count($this->{$key}) > 0) {
                return OpeningHoursOpeningHours::create(
                    data: array_merge(
                        $this->{$key},
                        [
                            'exceptions' => [],
                        ],
                    ),
                );
            }
        } catch (Exception) {
            //
        }

        return null;
    }
}
