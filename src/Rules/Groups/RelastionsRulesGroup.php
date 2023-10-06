<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules\Groups;

use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

class RelationsRulesGroup extends AbstractRulesGroup
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
                $this->prefix . 'fields' => [
                    'nullable',
                    'array',
                    Rule::in(
                        values: $this->schema->fields(),
                    ),
                ],
                $this->prefix . 'with' => [
                    'nullable',
                    'array',
                ],
                $this->prefix . 'with.*.relation' => [
                    'required',
                    Rule::in(
                        values: array_keys(
                            array: $this->schema->relations(),
                        ),
                    ),
                ],
                $this->prefix . 'with_count' => [
                    'nullable',
                    'array',
                    Rule::in(
                        values: $this->schema->countRelations(),
                    ),
                ],
            ],
        );

        if ($this->isRootSearchRules) {
            // $rules += [
            //     'limit' => [
            //         'sometimes',
            //         'integer',
            //         Rule::in(
            //             values: $this->schema->getLimits(
            //                 request: $this->request,
            //             ),
            //         ),
            //     ],
            //     'page' => [
            //         'sometimes',
            //         'integer',
            //         'min:1',
            //     ],
            //     'gates' => [
            //         'sometimes',
            //         'array',
            //         Rule::in(
            //             values: [
            //                 'viewAny',
            //                 'view',
            //                 'create',
            //                 'update',
            //                 'delete',
            //                 'restore',
            //                 'forceDelete',
            //             ],
            //         ),
            //         'includes' => [
            //             'sometimes',
            //             'array',
            //         ],
            //     ],
            // ]
            //     + IncludesRules::make(
            //         request: $this->request,
            //         schema: $this->schema,
            //         prefix: $this->prefix . 'includes',
            //     )
            //         ->toArray();
        }

        return $rules;
    }
}
