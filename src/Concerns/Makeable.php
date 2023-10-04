<?php

namespace Stepanenko3\LaravelApiSkeleton\Concerns;

trait Makeable
{
    /**
     * Create a new element.
     */
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }
}
