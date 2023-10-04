<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules\Groups;

use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;
use Stepanenko3\LaravelApiSkeleton\Rules\AggregateField;
use Stepanenko3\LaravelApiSkeleton\Rules\AggregateFilterable;

class AgreegatesRules extends AbstractRulesGroup
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
            $this->prefix . '.*.relation' => [
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
            $this->prefix . '.*.type' => [
                Rule::in(
                    values: ['count', 'min', 'max', 'avg', 'sum', 'exists'],
                ),
            ],
            $this->prefix . '.*.field' => [
                'required_if:' . $this->prefix . '.*.type,min,max,avg,sum',
                'prohibited_if:' . $this->prefix . '.*.type,count,exists',
            ],
            $this->prefix . '.*' => [
                'nullable',
                AggregateField::make()
                    ->schema(
                        schema: $this->schema,
                    ),
            ],
            $this->prefix . '.*.filters' => [
                'nullable',
                AggregateFilterable::make()
                    ->schema(
                        schema: $this->schema,
                    ),
            ],
        ];
    }
}
