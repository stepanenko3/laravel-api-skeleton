<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules\Groups;

use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

// @TODO: For now it's prohibited to have more than one nested depth, is this needed ?
class FiltersRules extends AbstractRulesGroup
{
    public bool $isMaxDepth = false;

    public function __construct(
        public Request $request,
        public Schema $schema,
        public string $prefix = '',
    ) {
        //
    }

    public function isMaxDepth(
        bool $value = true,
    ): self {
        $this->isMaxDepth = $value;

        return $this;
    }

    public function toArray(): array
    {
        $rules = [
            $this->prefix => [
                'sometimes',
                'array',
            ],
            $this->prefix . '.*.field' => [
                Rule::in(
                    values: $this->schema->getNestedFields(
                        request: $this->request,
                    ),
                ),
                "required_without:{$this->prefix}.*.nested",
                'string',
            ],
            $this->prefix . '.*.operator' => [
                Rule::in(
                    values: [
                        '=',
                        '!=',
                        '>',
                        '>=',
                        '<',
                        '<=',
                        'like',
                        'not like',
                        'in',
                        'not in',
                    ],
                ),
                'string',
            ],
            $this->prefix . '.*.value' => [
                "required_without:{$this->prefix}.*.nested",
            ],
            $this->prefix . '.*.type' => [
                'sometimes',
                Rule::in(
                    values: ['or', 'and'],
                ),
            ],
            $this->prefix . '.*.nested' => !$this->isMaxDepth ? [
                'sometimes',
                "prohibits:{$this->prefix}.*.field,{$this->prefix}.*.operator,{$this->prefix}.*.value",
                'prohibits:value',
                'array',
            ] : [
                'prohibited',
            ],
        ];

        if (!$this->isMaxDepth) {
            $rules += self::make(
                request: $this->request,
                schema: $this->schema,
                prefix: $this->prefix . '.*.nested',
            )
                ->isMaxDepth()
                ->toArray();
        }

        return $rules;
    }
}
