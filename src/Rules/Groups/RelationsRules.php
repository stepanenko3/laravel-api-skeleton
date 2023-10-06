<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules\Groups;

use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;
use Stepanenko3\LaravelApiSkeleton\Rules\Relationable;

class RelationsRules extends AbstractRulesGroup
{
    public function __construct(
        public Request $request,
        public Schema $schema,
        public string $prefix = '',
        public int $level = 0,
    ) {
    }

    public function toArray(): array
    {
        return [
            $this->prefix => [
                'nullable',
                'array',
            ],
            $this->prefix . '.*.relation' => [
                'required',
                Rule::in(
                    values: array_keys(
                        array: $this->schema->relations(),
                    ),
                ),
            ],
            $this->prefix . '.*' => [
                Relationable::make(
                    request: $this->request,
                    schema: $this->schema,
                    level: $this->level + 1,
                ),
            ],
        ];
    }
}
