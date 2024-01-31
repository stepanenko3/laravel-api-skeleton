<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Stepanenko3\LaravelApiSkeleton\Rules\Groups\SchemaRules;

class Request extends FormRequest
{
    public ?string $schema = null;

    public function validationData(): array
    {
        return array_merge(
            $this->route()->parameters(),
            $this->all(),
        );
    }

    protected function createDefaultValidator(
        ValidationFactory $factory,
    ) {
        $rules = method_exists($this, 'rules')
            ? $this->container->call([$this, 'rules'])
            : [];

        $rules += $this->schemaRules();

        $validator = $factory
            ->make(
                data: $this->validationData(),
                rules: $rules,
                messages: $this->messages(),
                attributes: $this->attributes(),
            )
            ->stopOnFirstFailure(
                stopOnFirstFailure: $this->stopOnFirstFailure,
            );

        if ($this->isPrecognitive()) {
            $validator->setRules(
                $this->filterPrecognitiveRules(
                    rules: $validator->getRulesWithoutPlaceholders(),
                ),
            );
        }

        return $validator;
    }

    private function schemaRules(): array
    {
        if (!$this->schema) {
            return [];
        }

        return SchemaRules::make(
            request: $this,
            schema: new $this->schema(),
        )
            ->toArray();
    }
}
