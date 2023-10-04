<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Stepanenko3\LaravelApiSkeleton\Concerns\Makeable;

class RequiredRelationOnCreation implements DataAwareRule, ValidationRule
{
    use Makeable;

    /**
     * Indicates whether the rule should be implicit.
     *
     * @var bool
     */
    public $implicit = true;

    /**
     * The schema related to.
     *
     * @var schema
     */
    protected $schema;

    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Set the data under validation.
     *
     * @param array<string, mixed> $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set the schema related to.
     *
     * @param mixed $schema
     *
     * @return $this
     */
    public function schema($schema)
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * Validate the attribute.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $arrayDot = Arr::dot($this->data);
        if (
            isset($arrayDot[Str::of($attribute)->beforeLast('.')->beforeLast('.')->append('.operation')->toString()])
            && $arrayDot[Str::of($attribute)->beforeLast('.')->beforeLast('.')->append('.operation')->toString()] === 'create'
            && (!isset($arrayDot[$attribute . '.0.operation']) && !isset($arrayDot[$attribute . '.operation']))
        ) {
            $fail('This relation is required on creation');
        }
    }
}
