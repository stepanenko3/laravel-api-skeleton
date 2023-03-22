<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers;

use Illuminate\Support\Arr;

class ApiPagination
{
    public function __construct(
        protected $pagination,
    ) {
        //
    }

    public function toArray(): array
    {
        return Arr::only(
            $this->pagination->toArray(),
            [
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
        );
    }
}
