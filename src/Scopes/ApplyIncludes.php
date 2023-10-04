<?php

namespace Stepanenko3\LaravelApiSkeleton\Scopes;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\DTO\IncludeDTO;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;
use Illuminate\Database\Eloquent\Relations\Relation;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder as EloquentBuilder;

final class ApplyIncludes
{
    public Request $request;

    public function __construct(
        protected array $includes,
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
        foreach ($this->includes as $include) {
            $this->applyInclude(
                builder: $builder,
                include: $include,
            );
        }
    }

    public function applyInclude(
        Builder | EloquentBuilder $builder,
        array $include,
    ): Builder | EloquentBuilder {
        return $builder
            ->with(
                relations: $include['relation'],
                callback: function (Relation $query) use ($include) {
                    $schema = $this->schema
                        ->relationSchema(
                            name: $include['relation'],
                        );

                    return $query->tap(
                        new ApplySearch(
                            schema: $schema,
                            dto: IncludeDTO::fromArray(
                                data: $include,
                            ),
                            isAuthorizingEnabled: $this->isAuthorizingEnabled,
                            request: $this->request,
                        )
                    );
                }
            );
    }
}
