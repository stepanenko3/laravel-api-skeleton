<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers;

use Illuminate\Support\Str;

class ApiPagination
{
    public function __construct(
        protected $pagination,
    ) {
        //
    }

    public function toArray(): array
    {
        return collect($this->pagination->toArray())
            ->except(keys: ['data'])
            ->keyBy(fn ($value, $key) => Str::snake($key))
            ->only(
                keys: [
                    'current_page',
                    'end',
                    'from',
                    'has_more_pages',
                    'has_pages',
                    'last_page',
                    'next_page',
                    'on_first_page',
                    'per_page',
                    'prev_page',
                    'progress',
                    'start',
                    'total',
                ],
            )
            ->toArray();
    }
}
