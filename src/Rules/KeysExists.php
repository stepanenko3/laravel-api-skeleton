<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Validation\Rules\DatabaseRule;

class KeysExists implements InvokableRule
{
    use Conditionable;
    use DatabaseRule;

    public function __invoke(string $attribute, mixed $value, Closure $fail): void
    {
        $keys = array_keys($value);

        $count = DB::table($this->table)
            ->whereIn($this->column, $keys)
            ->count();

        if ($count !== count($keys)) {
            $fail(trans(':attribute contains invalid fields'));
        }
    }
}
