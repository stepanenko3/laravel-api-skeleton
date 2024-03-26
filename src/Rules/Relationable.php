<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;
use Stepanenko3\LaravelApiSkeleton\Rules\Groups\SchemaRules;
use Stepanenko3\LaravelApiSkeleton\Traits\Makeable;

/** @phpstan-consistent-constructor */
class Relationable implements DataAwareRule, ValidationRule
{
    use Makeable;

    protected array $data;

    public function __construct(
        public Request $request,
        public Schema $schema,
        public int $level = 0,
    ) {
        //
    }

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
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

    public function setData(
        array $data,
    ): self {
        $this->data = $data;

        return $this;
    }

    protected function buildValidationRules(
        mixed $attribute,
        mixed $value,
    ): array {
        $relations = $this->schema->relations();

        $schema = $relations[$value['relation'] ?? null] ?? null;

        if (!$schema) {
            return [];
        }

        return SchemaRules::make(
            request: $this->request,
            schema: new $schema(),
            prefix: $attribute,
            level: $this->level,
        )
            ->isRootSearchRules(
                value: false,
            )
            ->toArray();
    }
}
