<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Schemas;

use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilderContract;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\DTO\SchemaDTO;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Traits\Authorizable;
use Stepanenko3\LaravelApiSkeleton\Traits\Makeable;

abstract class Schema
{
    use Authorizable;
    use Makeable;

    public function defaultFields(): array
    {
        return $this->fields();
    }

    public function defaultRelations(): array
    {
        return [];
    }

    public function defaultCountRelations(): array
    {
        return [];
    }

    public function performQuery(
        EloquentBuilderContract | QueryBuilderContract | Builder $query,
        SchemaDTO $dto,
    ): EloquentBuilderContract | QueryBuilderContract | Builder {
        return $query;
    }

    public function mutateDTO(
        SchemaDTO $dto,
    ): SchemaDTO {
        return $dto;
    }

    public function protectedRelations(): array
    {
        return [];
    }

    public function basicFields(): array
    {
        $propertyKey = 'basicFields';

        if (property_exists($this, $propertyKey)) {
            return $this->{$propertyKey};
        }

        return [];
    }

    public function basicFieldsWithDefaultFields(): array
    {
        return [
            ...$this->defaultFields(),
            ...$this->basicFields(),
        ];
    }

    public function getRelations(): array
    {
        return [
            ...$this->relations(),
            ...$this->protectedRelations(),
        ];
    }

    abstract public function fields(): array;

    abstract public function relations(): array;

    abstract public function countRelations(): array;

    public function defaultSort(): ?string
    {
        return null;
    }

    public function sorts(): array
    {
        return [];
    }

    public function scopes(
        Request $request,
    ): array {
        return [];
    }

    public function getScopes(
        Request $request,
    ): array {
        return $this->scopes(
            request: $request,
        );
    }

    public function limits(
        Request $request,
    ): array {
        return [
            1,
            5,
            10,
            15,
            20,
            25,
            50,
        ];
    }

    public function getLimits(
        Request $request,
    ): array {
        return $this->limits(
            request: $request,
        );
    }

    public function jsonSerialize(): mixed
    {
        $request = app(
            abstract: Request::class,
        );

        return [
            'fields' => $this->fields(),
            'limits' => $this->getLimits(
                request: $request,
            ),
            'realtions' => $this->relations(),
        ];
    }
}
