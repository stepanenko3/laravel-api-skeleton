<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\InvokableRule;

class RequiredArrayValues implements InvokableRule
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
        $unknownKeys = array_diff(
            $this->values,
            $value,
        );

        if (count($unknownKeys) !== 0) {
            $fail(trans('Values \'' . implode(', ', $this->values) . '\' in attribute :attribute is required'));
        }
    }
}
