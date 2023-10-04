<?php

namespace Stepanenko3\LaravelApiSkeleton\Relations\Traits;

trait HasMultipleResults
{
    public function hasMultipleEntries(): bool
    {
        return true;
    }
}
