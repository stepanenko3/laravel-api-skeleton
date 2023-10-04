<?php

namespace Stepanenko3\LaravelApiSkeleton\Scopes;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder as EloquentBuilder;
use Stepanenko3\LaravelApiSkeleton\DTO\SearchDTO;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

final class ApplyAggregates
{
    public Request $request;

    public function __construct(
        protected array $aggregates,
        protected Schema $schema,
        protected bool $isAuthorizingEnabled = true,
        ?Request $request = null,
    ) {
        $this->request = $request ?: app(
            abstract: Request::class,
        );
    }

    public function __invoke(
        Builder | EloquentBuilder $builder,
    ): void {
        foreach ($this->aggregates as $aggregate) {
            $this->applyAggregate(
                builder: $builder,
                aggregate: $aggregate,
            );
        }
    }

    public function applyAggregate(
        Builder | EloquentBuilder $builder,
        array $aggregate,
    ): Builder | EloquentBuilder {
        return $builder
            ->withAggregate(
                relations: [
                    $aggregate['relation'] => fn (Builder | EloquentBuilder $query) => $query->tap(
                        new ApplySearch(
                            schema: $this->schema
                                ->relationSchema(
                                    name: $aggregate['relation'],
                                ),
                            dto: SearchDTO::fromArray(
                                data: [
                                    'filters' => $aggregate['filters'] ?? [],
                                ],
                            ),
                            isAuthorizingEnabled: $this->isAuthorizingEnabled,
                            request: $this->request,
                        ),
                    ),
                ],
                column: $aggregate['field'] ?? '*',
                function: $aggregate['type'],
            );
    }
}
