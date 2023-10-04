<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Stepanenko3\LaravelApiSkeleton\Concerns\Makeable;
use Stepanenko3\LaravelApiSkeleton\Concerns\Schemable;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\SearchRequest;

class AggregateFilterable implements DataAwareRule, ValidationRule
{
    use Makeable;
    use Schemable;

    /**
     * The data under validation.
     */
    protected array $data;

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
     * Validate the attribute.
     */
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        $validator = FacadesValidator::make(
            data: $this->data,
            rules: $this->buildValidationRules(
                attribute: $attribute,
                value: $value,
            )
        );

        $validator->validate();
    }

    /**
     * Build the array of underlying validation rules based on the current state.
     */
    protected function buildValidationRules(
        mixed $attribute,
        mixed $value,
    ): array {
        if (null === $this->schema) {
            return [];
        }

        return app(SearchRequest::class)
            ->filtersRules(
                schema: $this->schema,
                prefix: $attribute,
            );
    }
}
