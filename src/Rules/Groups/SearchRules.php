<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules\Groups;

use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

class SearchRules extends AbstractRulesGroup
{
    public bool $isRootSearchRules = true;

    public function __construct(
        public Request $request,
        public Schema $schema,
        public string $prefix = '',
    ) {
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
            [
                $this->prefix . 'selects' => [
                    'sometimes',
                    'array',
                    Rule::in(
                        values: $this->schema->getFields(
                            request: $this->request,
                        ),
                    ),
                ],
            ],
            FiltersRules::make(
                request: $this->request,
                schema: $this->schema,
                prefix: $this->prefix . 'filters',
            )
                ->toArray(),
            ScopesRules::make(
                request: $this->request,
                schema: $this->schema,
                prefix: $this->prefix . 'scopes',
            )
                ->toArray(),
            SortsRules::make(
                request: $this->request,
                schema: $this->schema,
                prefix: $this->prefix . 'sorts',
            )
                ->toArray(),
            AgreegatesRules::make(
                request: $this->request,
                schema: $this->schema,
                prefix: $this->prefix . 'aggregates',
            )
                ->toArray(),
            InstructionsRules::make(
                request: $this->request,
                schema: $this->schema,
                prefix: $this->prefix . 'instructions',
            )
                ->toArray(),
        );

        if ($this->isRootSearchRules) {
            $rules += [
                'limit' => [
                    'sometimes',
                    'integer',
                    Rule::in(
                        values: $this->schema->getLimits(
                            request: $this->request,
                        ),
                    ),
                ],
                'page' => [
                    'sometimes',
                    'integer',
                    'min:1',
                ],
                'gates' => [
                    'sometimes',
                    'array',
                    Rule::in(
                        values: [
                            'viewAny',
                            'view',
                            'create',
                            'update',
                            'delete',
                            'restore',
                            'forceDelete',
                        ],
                    ),
                    'includes' => [
                        'sometimes',
                        'array',
                    ],
                ],
            ]
                + IncludesRules::make(
                    request: $this->request,
                    schema: $this->schema,
                    prefix: $this->prefix . 'includes',
                )
                    ->toArray();
        }

        return $rules;
    }
}
