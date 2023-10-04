<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Concerns\Makeable;
use Stepanenko3\LaravelApiSkeleton\Concerns\Schemable;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;

class AggregateField implements DataAwareRule, ValidationRule
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
        Closure $fail,
    ): void {
        $validator = FacadesValidator::make(
            data: $this->data,
            rules: $this->buildValidationRules(
                attribute: $attribute,
                value: $value,
            ),
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
        $relationSchema = $this->schema->relationSchema(
            name: $value['relation'],
        );

        if (null === $relationSchema) {
            return [];
        }

        return [
            $attribute . '.field' => Rule::in(
                values: $relationSchema
                    ->getFields(
                        request: app()->make(
                            abstract: Request::class,
                        ),
                    ),
            ),
        ];
    }
}
