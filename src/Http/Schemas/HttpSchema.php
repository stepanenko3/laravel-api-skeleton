<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Schemas;

abstract class HttpSchema
{
    public static function defaultFields(): array
    {
        return static::fields();
    }

    public static function defaultRelations(): array
    {
        return [];
    }

    public static function defaultCountRelations(): array
    {
        return [];
    }

    public static function basicFields(): array
    {
        if (property_exists(static::class, 'basicFields')) {
            return static::$basicFields;
        }

        return [];
    }

    abstract public static function fields(): array;

    abstract public static function relations(): array;

    abstract public static function countRelations(): array;
}
