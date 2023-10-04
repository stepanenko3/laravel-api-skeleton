<?php

namespace Stepanenko3\LaravelApiSkeleton\Scopes;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder as EloquentBuilder;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

final class ApplyScopes
{
    public Request $request;

    public function __construct(
        protected array $scopes,
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
        foreach ($this->scopes as $scope) {
            $this->applyScope(
                builder: $builder,
                name: $scope['name'],
                parameters: $scope['parameters'] ?? [],
            );
        }
    }

    public function applyScope(
        Builder | EloquentBuilder $builder,
        string $name,
        array $parameters = [],
    ): Builder | EloquentBuilder {
        return $builder
            ->{$name}(...$parameters);
    }
}
