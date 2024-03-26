<?php

namespace Stepanenko3\LaravelApiSkeleton\DTO;

use Illuminate\Http\Request;
use Illuminate\Support\Traits\Conditionable;
use Stepanenko3\LaravelApiSkeleton\Contracts\DtoContract;
use Stepanenko3\LaravelApiSkeleton\Traits\Makeable;

/** @phpstan-consistent-constructor */
abstract class DTO implements DtoContract
{
    use Conditionable;
    use Makeable;

    public function __construct()
    {
        //
    }

    public static function fromRequest(
        Request $request,
    ): static {
        $classVars = array_keys(
            array: get_class_vars(
                class: static::class,
            ),
        );

        return new static(
            ...array_filter(
                array: $request->validated(),
                callback: fn (string $key) => in_array(
                    needle: $key,
                    haystack: $classVars,
                ),
                mode: ARRAY_FILTER_USE_KEY,
            ),
        );
    }

    public static function fromArray(
        array $data,
    ): static {
        return new static(
            ...$data,
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function toBase(): self
    {
        return $this;
    }

    public function has(
        string $key,
    ): bool {
        return isset($this->{$key});
    }

    public function get(
        string $key,
        mixed $default = null,
    ): mixed {
        if (isset($this->{$key})) {
            return $this->{$key};
        }

        return $default;
    }
}
