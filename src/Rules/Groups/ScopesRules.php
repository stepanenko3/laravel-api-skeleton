<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules\Groups;

use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

class ScopesRules extends AbstractRulesGroup
{
    public function __construct(
        public Request $request,
        public Schema $schema,
        public string $prefix = '',
    ) {
    }

    public function toArray(): array
    {
        return [
            $this->prefix => [
                'sometimes',
                'array',
            ],
            $this->prefix . '.*.name' => [
                Rule::in(
                    values: $this->schema->getScopes(
                        request: $this->request,
                    ),
                ),
                'required',
                'string',
            ],
            $this->prefix . '.*.parameters' => [
                'sometimes',
                'array',
            ],
        ];
    }
}
