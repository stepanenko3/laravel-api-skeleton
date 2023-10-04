<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Validation\Rules\DatabaseRule;

class KeysExists implements ValidationRule
{
    use Conditionable;
    use DatabaseRule;

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        $keys = array_keys(
            array: $value,
        );

        $count = DB::table(
            table: $this->table,
        )
            ->whereIn(
                column: $this->column,
                values: $keys,
            )
            ->count();

        if ($count !== count($keys)) {
            $fail(trans(':attribute contains invalid fields'));
        }
    }
}
