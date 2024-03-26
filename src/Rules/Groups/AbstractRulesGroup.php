<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules\Groups;

use Stepanenko3\LaravelApiSkeleton\Traits\Makeable;

/** @phpstan-consistent-constructor */
abstract class AbstractRulesGroup
{
    use Makeable;

    public function __construct()
    {
        //
    }

    abstract public function toArray(): array;

    protected function withPrefix(
        array $rules,
        string $prefix,
    ): array {
        return array_combine(
            keys: array_map(
                callback: fn (string $key) => $prefix . $key,
                array: array_keys(
                    array: $rules,
                ),
            ),
            values: $rules,
        );
    }
}
