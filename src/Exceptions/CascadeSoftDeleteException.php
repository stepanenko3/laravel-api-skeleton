<?php

namespace Stepanenko3\LaravelApiSkeleton\Exceptions;

use Exception;
use Illuminate\Support\Str;

class CascadeSoftDeleteException extends Exception
{
    public static function softDeleteNotImplemented(
        $class
    ): self {
        return new static(
            message: sprintf('%s does not implement Illuminate\Database\Eloquent\SoftDeletes', $class),
        );
    }

    public static function invalidRelationships(
        array $relationships,
    ): self {
        return new static(
            message: sprintf(
                '%s [%s] must exist and return an object of type Illuminate\Database\Eloquent\Relations\Relation',
                Str::plural(
                    value: 'Relationship',
                    count: count($relationships),
                ),
                implode(
                    separator: ', ',
                    array: $relationships,
                )
            )
        );
    }
}
