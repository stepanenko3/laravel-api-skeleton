<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\InvokableRule;

class KeysIn implements InvokableRule
{
    /**
     * The accepted values.
     *
     * @var array
     */
    protected $values;

    /**
     * Create a new in rule instance.
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function __invoke(string $attribute, mixed $value, Closure $fail): void
    {
        $allowedKeys = array_flip($this->values);

        $unknownKeys = array_diff_key($value, $allowedKeys);

        if (count($unknownKeys) !== 0) {
            $fail(trans(':attribute contains invalid fields'));
        }
    }
}
