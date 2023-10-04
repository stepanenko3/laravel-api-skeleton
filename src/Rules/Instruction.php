<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Concerns\Makeable;
use Stepanenko3\LaravelApiSkeleton\Concerns\Schemable;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Instructions\Instruction as InstructionsInstruction;

class Instruction implements DataAwareRule, ValidationRule
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
        $validator = Validator::make(
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
        $instruction = $this->schema->instruction(
            request: app(
                abstract: Request::class,
            ),
            instructionKey: $value['name'] ?? '',
        );

        if (null === $instruction) {
            return [];
        }

        return [
            $attribute . '.name' => [
                Rule::in(
                    collect(
                        value: $this->schema
                            ->getInstructions(
                                request: app(
                                    abstract: Request::class,
                                ),
                            ),
                    )
                        ->map(
                            callback: fn (InstructionsInstruction $instruction) => $instruction->uriKey(),
                        )
                        ->toArray(),
                ),
            ],
            $attribute . '.fields.*.name' => [
                Rule::in(
                    values: array_keys(
                        array: $instruction->fields(
                            request: app(
                                abstract: Request::class,
                            ),
                        ),
                    ),
                ),
            ],
            $attribute . '.fields.*' => [
                InstructionField::make()
                    ->instruction(
                        instruction: $instruction,
                    ),
            ],
        ];
    }
}
