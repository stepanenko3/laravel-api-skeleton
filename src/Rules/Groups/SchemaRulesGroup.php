<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules\Groups;

use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;
use Stepanenko3\LaravelApiSkeleton\Rules\Includable;

class SchemaRulesGroup extends AbstractRulesGroup
{
    public bool $isRootSearchRules = true;

    public function __construct(
        public Request $request,
        public Schema $schema,
        public string $prefix = '',
        public int $level = 0,
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
        );

        if ($this->level <= 1) {
            $rules += [
                'with' => [
                    'nullable',
                    'array',
                ],
                'with.*.relation' => [
                    'required',
                    Rule::in(
                        values: array_keys(
                            array: $this->schema->relations(),
                        ),
                    ),
                ],
                'with.*' => [
                    Includable::make(
                        request: $this->request,
                        schema: $this->schema,
                        level: $this->level + 1,
                    ),
                ],
            ];
        }

        if ($this->isRootSearchRules) {
            $rules += [
                'page' => [
                    'sometimes',
                    'integer',
                    'min:1',
                ],
                'per_page' => [
                    'sometimes',
                    'integer',
                    Rule::in(
                        values: $this->schema->limits(
                            request: $this->request,
                        ),
                    ),
                ],
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
            ];
        }

        return $this->withPrefix(
            rules: $rules,
            prefix: $this->prefix,
        );
    }

    private function withPrefix(
        array $rules,
        string $prefix,
    ): array {
        return array_combine(
            keys: array_map(
                callback: fn (string $key) => $prefix . $key,
                array: array_keys(
                    array: $rules,
                ),
            ),
            values: $rules,
        );
    }
}
