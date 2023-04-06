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
        return static::countRelations();
    }

    public static function getAllowed(
        array $excludedSchemas = [],
    ): array {
        $fields = static::fields();
        $relations = [];
        $countRelations = static::countRelations();

        foreach (static::relations() as $name => $class) {
            if (in_array($class, $excludedSchemas)) {
                continue;
            }

            [$allowedFields, $allowedRelations, $allowdCountRelations] = $class::getAllowed(
                array_unique(
                    array_merge(
                        $excludedSchemas,
                        [static::class],
                    ),
                ),
            );

            $relations[$name] = [
                'fields' => $allowedFields,
                'with' => $allowedRelations,
            ];
        }

        return [$fields, $relations, $countRelations];
    }

    abstract public static function fields(): array;

    abstract public static function relations(): array;

    abstract public static function countRelations(): array;
}
