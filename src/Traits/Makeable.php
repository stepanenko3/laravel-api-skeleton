<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

trait Makeable
{
    public static function make(
        ...$arguments,
    ): static {
        return new static(
            ...$arguments,
        );
    }
}
