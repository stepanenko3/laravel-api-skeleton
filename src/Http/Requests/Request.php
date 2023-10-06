<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Stepanenko3\LaravelApiSkeleton\Rules\Groups\SchemaRulesGroup;

class Request extends FormRequest
{
    public string $schema;

    public function validationData(): array
    {
        return array_merge(
            $this->route()->parameters(),
            $this->all(),
        );
    }

    private function schemaRules(): array
    {
        return SchemaRulesGroup::make(
            request: $this,
            schema: new $this->schema,
        )
            ->toArray();
    }

    protected function createDefaultValidator(
        ValidationFactory $factory,
    ) {
        $rules = method_exists($this, 'rules')
            ? $this->container->call([$this, 'rules'])
            : [];

        $rules += $this->schemaRules();

        $validator = $factory->make(
            data: $this->validationData(),
            rules: $rules,
            messages: $this->messages(),
            attributes: $this->attributes(),
        )->stopOnFirstFailure(
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
}
