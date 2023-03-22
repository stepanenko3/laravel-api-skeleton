<?php

namespace Stepanenko3\LaravelLogicContainers\DTO;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Stepanenko3\LaravelLogicContainers\Exceptions\DTO\CastTargetException;
use Stepanenko3\LaravelLogicContainers\Exceptions\DTO\InvalidJsonException;
use Stepanenko3\LaravelLogicContainers\Exceptions\DTO\MissingCastTypeException;
use Illuminate\Support\Arr;
use Stepanenko3\LaravelLogicContainers\Interfaces\DtoCastInterface;
use Stepanenko3\LaravelLogicContainers\Interfaces\DtoInterface;

abstract class DTO implements DtoInterface
{
    protected array $validatedData = [];

    private \Illuminate\Contracts\Validation\Validator | \Illuminate\Validation\Validator $validator;

    public function __construct(
        protected array $data
    ) {
        $this->boot();

        $this->isValidData()
            ? $this->passedValidation()
            : $this->failedValidation();
    }

    public function __set(string $name, mixed $value): void
    {
        $this->{$name} = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->{$name} ?? null;
    }

    public function boot(): void
    {
        $this->bootTraits();
    }

    /**
     * Creates a DTO instance from a valid JSON string.
     *
     * @throws CastTargetException|InvalidJsonException|MissingCastTypeException|ValidationException
     *
     * @return $this
     */
    public static function fromJson(string $json): self
    {
        $jsonDecoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($jsonDecoded)) {
            throw new InvalidJsonException();
        }

        return new static($jsonDecoded);
    }

    /**
     * Creates a DTO instance from a Request.
     *
     * @throws CastTargetException|MissingCastTypeException|ValidationException
     *
     * @return $this
     */
    public static function fromRequest(Request $request): self
    {
        return new static([
            ...$request->route()->parameters(),
            ...$request->all(),
        ]);
    }

    /**
     * Creates a DTO instance from the given model.
     *
     * @throws CastTargetException|MissingCastTypeException|ValidationException
     *
     * @return $this
     */
    public static function fromModel(Model $model): self
    {
        return new static($model->toArray());
    }

    /**
     * Creates a DTO instance from the given command arguments.
     *
     * @throws CastTargetException|MissingCastTypeException|ValidationException
     *
     * @return $this
     */
    public static function fromCommandArguments(Command $command): self
    {
        return new static($command->arguments());
    }

    /**
     * Creates a DTO instance from the given command options.
     *
     * @throws CastTargetException|MissingCastTypeException|ValidationException
     *
     * @return $this
     */
    public static function fromCommandOptions(Command $command): self
    {
        return new static($command->options());
    }

    /**
     * Creates a DTO instance from the given command arguments and options.
     *
     * @throws CastTargetException|MissingCastTypeException|ValidationException
     *
     * @return $this
     */
    public static function fromCommand(Command $command): self
    {
        return new static(array_merge($command->arguments(), $command->options()));
    }

    /**
     * Returns the DTO validated data in array format.
     */
    public function toArray(): array
    {
        return $this->validatedData;
    }

    /**
     * Returns the DTO validated data in a JSON string format.
     */
    public function toJson(bool $pretty = false): string
    {
        return $pretty
            ? json_encode($this->validatedData, JSON_PRETTY_PRINT)
            : json_encode($this->validatedData, JSON_THROW_ON_ERROR);
    }

    /**
     * Creates a new model with the DTO validated data.
     */
    public function toModel(string $model): Model
    {
        return new $model($this->validatedData);
    }

    /**
     * Defines the custom messages for validator errors.
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Defines the custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [];
    }

    protected function bootTraits(): void
    {
        $class = static::class;

        $booted = [];

        foreach (class_uses_recursive($class) as $trait) {
            $method = 'boot' . class_basename($trait);

            if (method_exists($class, $method) && !in_array($method, $booted)) {
                $this->{$method}();

                $booted[] = $method;
            }
        }
    }

    /**
     * Defines the validation rules for the DTO.
     */
    abstract protected function rules(): array;

    /**
     * Defines the default values for the properties of the DTO.
     */
    abstract protected function defaults(): array;

    /**
     * Defines the type casting for the properties of the DTO.
     */
    abstract protected function casts(): array;

    /**
     * Handles a passed validation attempt.
     *
     * @throws CastTargetException|MissingCastTypeException
     */
    protected function passedValidation(): void
    {
        $this->validatedData = $this->validatedData();

        $result = self::dot($this->validatedData);

        $casts = self::dot(
            $this->casts(),
        );

        $defaults = self::dot(
            $this->defaults(),
        );

        foreach ($defaults as $key => $value) {
            if (
                !isset($result[$key])
                || empty($result[$key])
            ) {
                if (!array_key_exists($key, $casts)) {
                    if (config('logic-containers.dto_require_casting', false)) {
                        throw new MissingCastTypeException($key);
                    }

                    $result[$key] = $value;

                    continue;
                }

                if (!$casts[$key] instanceof DtoCastInterface) {
                    throw new CastTargetException($key);
                }

                $formatted = $casts[$key]->cast($key, $value);

                $result[$key] = $formatted;
            }
        }

        $result = Arr::undot($result);

        foreach ($result as $key => $value) {
            $this->{$key} = $value;
        }

        $this->validatedData = $result;
    }

    /**
     * Handles a failed validation attempt.
     *
     * @throws ValidationException
     */
    protected function failedValidation(): void
    {
        throw new ValidationException($this->validator);
    }

    protected static function dot(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value) && !Arr::isList($value)) {
                $results = array_merge(
                    $results,
                    static::dot($value, $prepend . $key . '.'),
                );
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * Checks if the data is valid for the DTO.
     */
    private function isValidData(): bool
    {
        $this->validator = Validator::make(
            $this->data,
            $this->rules(),
            $this->messages(),
            $this->attributes()
        );

        return $this->validator->passes();
    }

    /**
     * Builds the validated data from the given data and the rules.
     *
     * @throws CastTargetException|MissingCastTypeException
     */
    private function validatedData(): array
    {
        $acceptedKeys = array_keys($this->rules());
        $result = [];

        /** @var array<Castable> $casts */
        $casts = $this->casts();

        $data = self::dot($this->data);

        foreach ($data as $key => $value) {
            if (in_array($key, $acceptedKeys)) {
                if (!array_key_exists($key, $casts)) {
                    if (config('app.dto_require_casting', false)) {
                        throw new MissingCastTypeException($key);
                    }

                    $result[$key] = $value;

                    continue;
                }

                if (!$casts[$key] instanceof DtoCastInterface) {
                    throw new CastTargetException($key);
                }

                $result[$key] = $casts[$key]->cast($key, $value);
            }
        }

        return Arr::undot($result);
    }
}
