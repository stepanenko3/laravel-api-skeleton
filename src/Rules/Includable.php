<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Stepanenko3\LaravelApiSkeleton\Concerns\Makeable;
use Stepanenko3\LaravelApiSkeleton\Concerns\Schemable;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\SearchRequest;
use Stepanenko3\LaravelApiSkeleton\Rules\Groups\SearchRules;

class Includable implements DataAwareRule, ValidationRule
{
    use Makeable;
    use Schemable;

    /**
     * The data under validation.
     */
    protected array $data;

    /**
     * Determine if the validation rule passes.
     */
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        $validator = Validator::make(
            $this->data,
            $this->buildValidationRules($attribute, $value)
        );

        $validator->validate();
    }

    /**
     * Set the current data under validation.
     */
    public function setData(
        array $data,
    ): self {
        $this->data = $data;

        return $this;
    }

    /**
     * Build the array of underlying validation rules based on the current state.
     */
    protected function buildValidationRules(
        mixed $attribute,
        mixed $value,
    ): array {
        $relationSchema = $this->schema
            ->relationSchema(
                name: $value['relation'],
            );

        if (null === $relationSchema) {
            return [];
        }

        return SearchRules::make(
            request: app(
                abstract: SearchRequest::class,
            ),
            schema: $relationSchema,
            prefix: $attribute,
        )
            ->isRootSearchRules(
                value: false,
            )
            ->toArray();
    }
}
