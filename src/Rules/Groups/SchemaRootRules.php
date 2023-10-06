<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules\Groups;

use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

class SchemaRootRules extends AbstractRulesGroup
{
    public function __construct(
        public Request $request,
        public Schema $schema,
    ) {
    }

    public function toArray(): array
    {
        return [
            'page' => [
                'sometimes',
                'integer',
                'min:1',
            ],
            'per_page' => [
                'sometimes',
                'integer',
                Rule::in(
                    values: $this->schema->getLimits(
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
}
