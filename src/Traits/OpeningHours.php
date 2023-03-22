<?php

namespace Stepanenko3\LaravelLogicContainers\Traits;

use Spatie\OpeningHours\OpeningHours as OpeningHoursOpeningHours;

trait OpeningHours
{
    public function openingHours(string $key = 'opening_hours')
    {
        try {
            return $this->{$key} && count($this->{$key}) > 0
                ? OpeningHoursOpeningHours::create(
                    array_merge(
                        $this->{$key},
                        [
                            'exceptions' => [],
                        ],
                    ),
                )
                : null;
        } catch (\Exception $e) {
            return;
        }
    }
}
