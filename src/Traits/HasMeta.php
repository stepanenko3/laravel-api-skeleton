<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Stepanenko3\LaravelApiSkeleton\Models\Seo\SeoMeta;

trait HasMeta
{
    public function meta(): MorphOne
    {
        return $this
            ->morphOne(
                related: SeoMeta::class,
                name: 'for',
            )
            ->latest();
    }
}
