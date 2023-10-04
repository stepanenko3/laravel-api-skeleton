<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Stepanenko3\LaravelApiSkeleton\Concerns\Makeable;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;

class InstructionField implements DataAwareRule, ValidationRule, ValidatorAwareRule
{
    use Makeable;

    /**
     * The data under validation.
     *
     * @var array
     */
    protected $data;

    /**
     * The error message after validation, if any.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * The schema related to.
     */
    protected \Stepanenko3\LaravelApiSkeleton\Instructions\Instruction $instruction;

    /**
     * The validator performing the validation.
     *
     * @var \Illuminate\Validation\Validator
     */
    protected $validator;

    /**
     * @return $this
     */
    public function instruction(\Stepanenko3\LaravelApiSkeleton\Instructions\Instruction $instruction)
    {
        $this->instruction = $instruction;

        return $this;
    }

    /**
     * Get the validation error message.
     *
     * @return array
     */
    public function message()
    {
        return $this->messages;
    }

    /**
     * Set the current validator.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return $this
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Set the current data under validation.
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Validate the attribute.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $validator = Validator::make(
            $this->data,
            $this->buildValidationRules($attribute, $value)
        );

        $validator->validate();
    }

    /**
     * Build the array of underlying validation rules based on the current state.
     *
     * @param mixed $attribute
     * @param mixed $value
     *
     * @return array
     */
    protected function buildValidationRules($attribute, $value)
    {
        $field = $this->instruction->field(app(Request::class), $value['name'] ?? '');

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
