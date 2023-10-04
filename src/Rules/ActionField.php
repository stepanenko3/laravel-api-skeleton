<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Validation\Validator;
use Stepanenko3\LaravelApiSkeleton\Actions\Action;
use Stepanenko3\LaravelApiSkeleton\Concerns\Makeable;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;

class ActionField implements DataAwareRule, ValidationRule, ValidatorAwareRule
{
    use Makeable;

    /**
     * The data under validation.
     */
    protected array $data;

    /**
     * The error message after validation, if any.
     */
    protected array $messages = [];

    /**
     * The schema related to.
     */
    protected Action $action;

    /**
     * The validator performing the validation.
     */
    protected Validator $validator;

    public function action(Action $action): self
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get the validation error message.
     */
    public function message(): array
    {
        return $this->messages;
    }

    /**
     * Set the current validator.
     */
    public function setValidator(
        Validator $validator,
    ): self {
        $this->validator = $validator;

        return $this;
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
            ),
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
        $field = $this->action->field(app(Request::class), $value['name'] ?? '');

        if (null === $field) {
            return [];
        }

        return [
            $attribute . '.value' => $field,
        ];
    }

    /**
     * Adds the given failures, and return false.
     *
     * @param array|string $messages
     *
     * @return bool
     */
    protected function fail($messages)
    {
        $messages = collect(Arr::wrap($messages))->map(fn ($message) => $this->validator->getTranslator()->get($message))->all();

        $this->messages = array_merge($this->messages, $messages);

        return false;
    }
}
