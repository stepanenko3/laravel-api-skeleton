<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules\Groups;

use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

class SchemaRules extends AbstractRulesGroup
{
    public bool $isRootSearchRules = true;

    public function __construct(
        public Request $request,
        public Schema $schema,
        public string $prefix = '',
        public int $level = 0,
    ) {
        //
    }

    public function isRootSearchRules(
        bool $value = true,
    ): self {
        $this->isRootSearchRules = $value;

        return $this;
    }

    public function toArray(): array
    {
        if ($this->prefix !== '') {
            $this->prefix .= '.';
        }

        $rules = array_merge(
            $this->withPrefix(
                prefix: $this->prefix,
                rules: [
                    'fields' => [
                        'nullable',
                        'array',
                        Rule::in(
                            values: $this->schema->fields(),
                        ),
                    ],
                    'with_count' => [
                        'nullable',
                        'array',
                        Rule::in(
                            values: $this->schema->countRelations(),
                        ),
                    ],
                ],
            ),
            ScopesRules::make(
                request: $this->request,
                schema: $this->schema,
                prefix: $this->prefix . 'scopes',
            )
                ->toArray(),
        );

        if ($this->level <= 1) {
            $rules +=
                RelationsRules::make(
                    request: $this->request,
                    schema: $this->schema,
                    prefix: $this->prefix . 'with',
                    level: $this->level,
                )
                    ->toArray();
        }

        if ($this->isRootSearchRules) {
            $rules +=
                SchemaRootRules::make(
                    request: $this->request,
                    schema: $this->schema,
                )
                    ->toArray();
        }

        return $rules;
    }
}
