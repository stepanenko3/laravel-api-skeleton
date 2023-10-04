<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules\Groups;

use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;
use Stepanenko3\LaravelApiSkeleton\Rules\Includable;

class IncludesRules extends AbstractRulesGroup
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
            'includes.*.relation' => [
                'required',
                Rule::in(
                    values: array_keys(
                        $this->schema->nestedRelations(
                            request: app()->make(
                                abstract: Request::class,
                            ),
                        ),
                    ),
                ),
            ],
            'includes.*.includes' => [
                'prohibited',
            ],
            'includes.*' => [
                'nullable',
                Includable::make()
                    ->schema(
                        schema: $this->schema,
                    ),
            ],
        ];
    }
}
