<?php

namespace Stepanenko3\LaravelApiSkeleton\DTO;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastTargetException;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\InvalidJsonException;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\MissingCastTypeException;
use ReflectionObject;
use ReflectionProperty;
use Stepanenko3\LaravelApiSkeleton\Interfaces\DtoCastInterface;
use Stepanenko3\LaravelApiSkeleton\Interfaces\DtoInterface;

abstract class DTO implements DtoInterface
{
    protected array $properties = [];

    protected array $validated = [];

    public function __construct(
        protected array $data,
    ) {
        $this->boot();

        $this->properties = $this->getProperties();

        $this->validated = $this->validate(
            $this->data,
        );
    }

    public function __set(string $name, mixed $value): void
    {
        $this->setAttribute($name, $value);
    }

    public function __get(string $name): mixed
    {
        return $this->getAttribute($name);
    }

    public function __call(string $name, array $values)
    {
        if (preg_match('~^(set|get)([A-Z])(.*)$~', $name, $matches)) {
            $split = preg_split('/(?=[A-Z])/', $name);
            $property = strtolower(implode('_', array_slice($split, 1)));

            switch ($matches[1]) {
                case 'set':
                    return $this->setAttribute($property, $values[0]);

                case 'get':
                    return $this->getAttribute($property);
            }
        }

        return $this->{$name}(...$values);
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

    public function boot(): void
    {
        $this->bootTraits();
    }

    public function setAttribute(string $attribute, mixed $value): self
    {
        if (!in_array($attribute, $this->properties)) {
            throw new Exception(static::class . ' has no attribute named "' . $attribute . '"');
        }

        $this->{$attribute} = $value;

        return $this;
    }

    public function getAttribute(string $attribute): mixed
    {
        if (!in_array($attribute, $this->properties)) {
            throw new Exception(static::class . ' has no attribute named "' . $attribute . '"');
        }

        if ($this->isPropInitialized($attribute)) {
            return $this->{$attribute};
        }

        return null;
    }

    /**
     * Returns the DTO validated data in array format.
     */
    public function toArray(): array
    {
        return $this->validated;
    }

    /**
     * Returns the DTO validated data in a JSON string format.
     */
    public function toJson(bool $pretty = false): string
    {
        return $pretty
            ? json_encode($this->validated, JSON_PRETTY_PRINT)
            : json_encode($this->validated, JSON_THROW_ON_ERROR);
    }

    /**
     * Creates a new model with the DTO validated data.
     */
    public function toModel(string $model): Model
    {
        return new $model($this->validated);
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
     * Defines the type casting for the properties of the DTO.
     */
    abstract protected function casts(): array;

    protected function validate(array $data): array
    {
        return Pipeline::send(
            passable: $data,
        )->through([
            fn ($passable, $next) => $next($this->getValidatedData($passable)),
            fn ($passable, $next) => $next($this->applyDefaults($passable)),
            fn ($passable, $next) => $next($this->applyCasts($passable)),
            fn ($passable, $next) => $next($this->applyProps($passable)),
        ])->thenReturn();
    }

    protected function getValidatedData(array $data): array
    {
        $validator = Validator::make(
            $data,
            $this->rules(),
            $this->messages(),
            $this->attributes()
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    protected function applyDefaults(array $data): array
    {
        foreach ($this->properties as $prop) {
            if (isset($data[$prop]) && !empty($data[$prop])) {
                continue;
            }

            if ($this->isPropInitialized($prop)) {
                $data[$prop] = $this->{$prop};
            }
        }

        return $data;
    }

    protected function applyCasts(array $data): array
    {
        $casts = $this->casts();

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (!array_key_exists($key, $casts)) {
                if (config('laravel-api-skeleton.dto_require_casting', false)) {
                    throw new MissingCastTypeException($key);
                }

                $data[$key] = $value;

                continue;
            }

            if (!$casts[$key] instanceof DtoCastInterface) {
                throw new CastTargetException($key);
            }

            $data[$key] = $casts[$key]->cast($key, $value);
        }

        return $data;
    }

    protected function applyProps(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            $this->{$key} = $value;
        }

        return $data;
    }

    private function getProperties(): array
    {
        $properties = (new ReflectionObject($this))
            ->getProperties();

        $properties = array_filter(
            $properties,
            fn ($prop) => $prop->class !== self::class,
        );

        return array_map(
            fn ($prop) => $prop->name,
            $properties,
        );
    }

    private function isPropInitialized(string $name)
    {
        return (new ReflectionProperty($this, $name))->isInitialized($this);
    }
}
