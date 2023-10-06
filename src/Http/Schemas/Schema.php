<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Schemas;

use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilderContract;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\DTO\SchemaDTO;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Traits\Authorizable;

abstract class Schema
{
    use Authorizable;

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

    abstract public function fields(): array;

    abstract public function relations(): array;

    abstract public function countRelations(): array;

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

    public function jsonSerialize(): mixed
    {
        $request = app(
            abstract: Request::class,
        );

        return [
            'fields' => $this->fields(),
            'limits' => $this->limits(
                request: $request,
            ),
            'realtions' => $this->relations(),
        ];
    }
}
