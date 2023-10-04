<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules\Groups;

use Illuminate\Validation\Rule;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;
use Stepanenko3\LaravelApiSkeleton\Rules\Instruction;

class InstructionsRules extends AbstractRulesGroup
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
                    values: collect(
                        value: $this->schema->getInstructions(
                            request: $this->request,
                        ),
                    )
                        ->map(
                            callback: fn ($instruction) => $instruction->uriKey(),
                        )
                        ->toArray()
                ),
                'required',
                'string',
            ],
            $this->prefix . '.*.fields' => [
                'sometimes',
                'array',
            ],
            $this->prefix . '.*' => [
                'nullable',
                Instruction::make()
                    ->schema(
                        schema: $this->schema,
                    ),
            ],
        ];
    }
}
