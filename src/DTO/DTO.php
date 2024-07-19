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

    public array $keys = [];

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

        $static = new static(
            ...array_filter(
                array: $request->validated(),
                callback: fn (string $key) => in_array(
                    needle: $key,
                    haystack: $classVars,
                ),
                mode: ARRAY_FILTER_USE_KEY,
            ),
        );

        $static->keys = array_keys(
            array: $request->validated(),
        );

        return $static;
    }

    public static function fromArray(
        array $data,
    ): static {
        $static = new static(
            ...$data,
        );

        $static->keys = array_keys(
            array: $data,
        );

        return $static;
    }

    public function toArray(
        bool $intersection = false,
    ): array {
        $vars = get_object_vars($this);

        return $intersection
            ?  array_intersect_key(
                $vars, array_flip($this->keys))
            : $vars;
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

    public function __set(
        string $name,
        mixed $value,
    ): void {
        $this->{$name} = $value;

        $this->keys = array_unique(
            array: array_merge(
                $this->keys,
                [$name],
            ),
        );
    }
}
