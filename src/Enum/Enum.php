<?php

namespace Stepanenko3\LaravelApiSkeleton\Enum;

use ReflectionClass;

abstract class Enum
{
    final public static function getAll(): array
    {
        return array_values(
            array: static::toArray(),
        );
    }

    final public static function toArray(): array
    {
        return (new ReflectionClass(static::class))
            ->getConstants();
    }

    final public static function getConst(): array
    {
        return array_keys(
            array: static::toArray(),
        );
    }

    final public static function isValid($value): bool
    {
        return in_array(
            needle: $value,
            haystack: static::toArray(),
        );
    }

    final public static function isValidConst($value): bool
    {
        return in_array(
            needle: $value,
            haystack: static::getConst(),
        );
    }

    final public static function getValue($const): mixed
    {
        return static::toArray()[$const];
    }
}
