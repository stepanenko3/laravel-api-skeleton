<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Schemas;

use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\CastTargetException;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\InvalidJsonException;
use Stepanenko3\LaravelApiSkeleton\Exceptions\DTO\MissingCastTypeException;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Rules\KeysIn;

abstract class HttpSchema
{
    protected array $validatedData = [];

    private \Illuminate\Contracts\Validation\Validator | \Illuminate\Validation\Validator $validator;

    public function __construct(
        protected array $data = [],
        protected bool $standalone = false,
    ) {
        if (!$this->standalone) {
            $this->boot();
            $this->bootTraits();

            $this->isValidData()
                ? $this->passedValidation()
                : $this->failedValidation();
        }
    }

    public function __set(string $name, mixed $value): void
    {
        $this->{$name} = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->{$name} ?? null;
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

    public function boot(): void
    {
        //
    }

    public function getFields(): array
    {
        return !empty($this->validatedData['fields'] ?? [])
            ? $this->validatedData['fields']
            : $this->fields();
    }

    public function getRelations(): array
    {
        return $this->validatedData['with'] ?? [];
    }

    public function getCountRelations(): array
    {
        return $this->validatedData['with_count'] ?? [];
    }

    public function applyToQuery(EloquentBuilder | QueryBuilder $builder): EloquentBuilder | QueryBuilder
    {
        return $builder
            ->select($this->getFields())
            ->with($this->relationsToQuery());
    }

    public function relationsToQuery(array $relations = []): array
    {
        $data = [];

        foreach (($this->getRelations() ?: $relations) as $key => $relation) {
            $data[$key] = fn ($q) => $q
                ->select($realtion['fields'] ?? [])
                ->with($this->relationsToQuery($relation['with'] ?? []));
        }

        return $data;
    }

    public function getAllowed(
        array $excludedSchemas = [],
    ): array {
        $fields = $this->fields();
        $relations = [];
        $countRelations = $this->withCount();

        foreach ($this->with() as $name => $class) {
            if (in_array($class, $excludedSchemas)) {
                continue;
            }

            [$allowedFields, $allowedRelations, $allowdCountRelations] = (new $class(standalone: true))
                ->getAllowed(
                    array_unique(
                        array_merge(
                            $excludedSchemas,
                            [static::class],
                        ),
                    ),
                );

            $relations[$name] = [
                'fields' => $allowedFields,
                'with' => $allowedRelations,
            ];
        }

        return [$fields, $relations, $countRelations];
    }

    public function rules(): array
    {
        [$fields, $relations, $countRelations] = $this->getAllowed();

        return [
            'fields' => ['nullable', 'array'],
            'fields.*' => [
                'required',
                'string',
                Rule::in($fields),
            ],

            'with' => ['nullable', 'array'],

            ...$this->relationsToRules($relations),
        ];
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

    abstract protected function fields(): array;

    abstract protected function with(): array;

    abstract protected function withCount(): array;

    protected function defaults(): array
    {
        return [
            'fields' => $this->fields(),
            'with' => array_keys($this->with()),
            'with_count' => $this->withCount(),
        ];
    }

    /**
     * Handles a passed validation attempt.
     *
     * @throws CastTargetException|MissingCastTypeException
     */
    protected function passedValidation(): void
    {
        $result = $this->validator->validated();

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

    private function relationsToRules(array $relations, string $prefix = ''): array
    {
        $rules = [
            $prefix . 'with' => [
                'nullable',
                'array',
                new KeysIn(array_keys($relations)),
            ],
        ];

        foreach ($relations as $relationName => $relation) {
            $key = $prefix . 'with.' . $relationName;

            $rules[$key] = [
                'nullable',
                'array',
            ];

            if (!empty($relation['fields'] ?? [])) {
                $rules[$key . '.fields'] = [
                    'required_with:' . $key,
                    'array',
                    Rule::in($relation['fields']),
                ];
            }

            if (!empty($relation['with'] ?? [])) {
                $rules[$key . '.with'] = [
                    'nullable',
                    'array',
                ];

                $rules = array_merge(
                    $rules,
                    $this->relationsToRules(
                        $relation['with'],
                        $key . '.',
                    ),
                );
            }
        }

        return $rules;
    }
}
