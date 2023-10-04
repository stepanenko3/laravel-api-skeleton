<?php

namespace Stepanenko3\LaravelApiSkeleton\Scopes;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;
use Stepanenko3\LaravelApiSkeleton\Concerns\Makeable;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder as EloquentBuilder;
use Stepanenko3\LaravelApiSkeleton\DTO\IncludeDTO;
use Stepanenko3\LaravelApiSkeleton\DTO\SearchDTO;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

final class ApplySearch
{
    use Conditionable;
    use Makeable;
    use Tappable;

    public Request $request;

    public function __construct(
        protected Schema $schema,
        protected SearchDTO | IncludeDTO $dto,
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
        if ($this->isAuthorizingEnabled) {
            $this->schema->authorizeTo(
                ability: 'viewAny',
                model: $builder->getModel(),
            );
        }

        $this->schema->searchQuery(
            request: $this->request,
            dto: $this->dto,
            query: $builder,
        );

        $builder
            ->when(
                value: $this->dto->filters,
                callback: fn (Builder | EloquentBuilder $builder) => $builder->where(
                    fn (Builder | EloquentBuilder $builder) => $builder->tap(
                        new ApplyFilters(
                            filters: $this->dto->filters,
                            schema: $this->schema,
                            request: $this->request,
                        ),
                    ),
                ),
            )
            ->tap(
                new ApplySorts(
                    sorts: $this->dto->sorts,
                    schema: $this->schema,
                    request: $this->request,
                ),
            )
            ->when(
                value: !empty($this->dto->scopes),
                callback: fn (Builder | EloquentBuilder $builder) => $builder->tap(
                    new ApplyScopes(
                        scopes: $this->dto->scopes,
                        schema: $this->schema,
                        request: $this->request,
                    ),
                ),
            )
            ->when(
                value: !empty($this->dto->instructions),
                callback: fn (Builder | EloquentBuilder $builder) => $builder->tap(
                    new ApplyInstructions(
                        instructions: $this->dto->instructions,
                        schema: $this->schema,
                        request: $this->request,
                    ),
                ),
            )
            ->when(
                value: !empty($this->dto->includes),
                callback: fn (Builder | EloquentBuilder $builder) => $builder->tap(
                    new ApplyIncludes(
                        includes: $this->dto->includes,
                        schema: $this->schema,
                        request: $this->request,
                        isAuthorizingEnabled: $this->isAuthorizingEnabled,
                    ),
                ),
            )
            ->when(
                value: !empty($this->dto->aggregates),
                callback: fn (Builder | EloquentBuilder $builder) => $builder->tap(
                    new ApplyAggregates(
                        aggregates: $this->dto->aggregates,
                        schema: $this->schema,
                        request: $this->request,
                        isAuthorizingEnabled: $this->isAuthorizingEnabled,
                    ),
                ),
            );
    }
}
