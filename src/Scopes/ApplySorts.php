<?php

namespace Stepanenko3\LaravelApiSkeleton\Scopes;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder as EloquentBuilder;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

final class ApplySorts
{
    public Request $request;

    public function __construct(
        protected array $sorts,
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
        $builder->when(
            value: empty($this->sorts),
            callback: function (Builder | EloquentBuilder $builder): void {
                $defaultOrderBy = $this->schema->defaultOrderBy(
                    request: $this->request,
                );

                foreach ($defaultOrderBy as $column => $order) {
                    $builder->orderBy(
                        $builder->getModel()->getTable() . '.' . $column,
                        $order,
                    );
                }
            },
            default: function (Builder | EloquentBuilder $builder): void {
                foreach ($this->sorts as $sort) {
                    $this->applySort(
                        builder: $builder,
                        direction: $sort['direction'] ?? 'asc',
                        field: $builder
                            ->getModel()
                            ->getTable() . '.' . $sort['field'],
                    );
                }
            }
        );
    }

    public function applySort(
        Builder | EloquentBuilder $builder,
        string $field,
        string $direction = 'asc',
    ): Builder | EloquentBuilder {
        return $builder
            ->orderBy(
                $field,
                $direction,
            );
    }
}
