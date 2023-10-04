<?php

namespace Stepanenko3\LaravelApiSkeleton\Scopes;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;
use Illuminate\Support\Str;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder as EloquentBuilder;

final class ApplyFilters
{
    public Request $request;

    public function __construct(
        protected array $filters,
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
        foreach ($this->filters as $filter) {
            $this->applyFilter(
                builder: $builder,
                field: $filter['field'] ?? null,
                operator: $filter['operator'] ?? '=',
                value: $filter['value'] ?? null,
                type: $filter['type'] ?? 'and',
                nested: $filter['nested'] ?? null,
            );
        }
    }

    public function applyFilter(
        Builder | EloquentBuilder $builder,
        string $field,
        string $operator,
        mixed $value,
        string $type = 'and',
        array | null $nested = null,
    ) {
        if ($nested !== null) {
            return $builder
                ->where(
                    column: fn (Builder | EloquentBuilder $builder) => $builder->tap(
                        new self(
                            filters: $nested,
                            schema: $this->schema,
                            request: $this->request,
                        ),
                    ),
                    boolean: $type,
                );
        }

        // Here we assume the user has asked a relation filter
        if (Str::contains(
            haystack: $field,
            needles: '.',
        )) {
            $relation = $this->schema->relation(
                name: $field,
            );

            return $relation->filter(
                query: $builder,
                relation: $field,
                operator: $operator,
                value: $value,
                boolean: $type,
                callback: function ($query) use ($relation): void {
                    $relation->applySearchQuery(
                        query: $query,
                    );
                },
            );
        } else {
            if (in_array($operator, ['in', 'not in'])) {
                $builder->whereIn(
                    $builder->getModel()->getTable() . '.' . $field,
                    $value,
                    $type,
                    $operator === 'not in'
                );
            } else {
                $builder->where(
                    column: $builder->getModel()->getTable() . '.' . $field,
                    operator: $operator,
                    value: $value,
                    boolean: $type,
                );
            }
        }
    }
}
