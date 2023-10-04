<?php

namespace Stepanenko3\LaravelApiSkeleton\Scopes;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder as EloquentBuilder;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

final class ApplyInstructions
{
    public Request $request;

    public function __construct(
        protected array $instructions,
        protected Schema $schema,
        ?Request $request = null,
    ) {
        $this->request = $request ?: app(
            abstract: Request::class,
        );
    }

    public function __invoke(
        Builder | EloquentBuilder $builder,
    ): void {
        foreach ($this->instructions as $instruction) {
            $this->applyInstruction(
                builder: $builder,
                name: $instruction['name'],
                fields: $instruction['fields'] ?? [],
            );
        }
    }

    public function applyInstruction(
        Builder | EloquentBuilder $builder,
        string $name,
        array $fields = [],
    ): void {
        $this->schema
            ->instruction(
                request: $this->request,
                instructionKey: $name,
            )
            ->handle(
                query: $builder,
                fields: collect($fields)
                    ->mapWithKeys(
                        fn ($field) => [
                            $field['name'] => $field['value'],
                        ],
                    )
                    ->toArray(),
            );
    }
}
