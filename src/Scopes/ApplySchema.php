<?php

namespace Stepanenko3\LaravelApiSkeleton\Scopes;

use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilderContract;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\DTO\ApplySchemaDTO;
use Stepanenko3\LaravelApiSkeleton\DTO\SchemaDTO;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;
use Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\ApplySchemaProcess;

class ApplySchema
{
    public Request $request;

    public function __construct(
        protected Schema $schema,
        protected SchemaDTO $dto,
    ) {
        //
    }

    public function __invoke(
        EloquentBuilderContract | QueryBuilderContract | Builder $builder,
    ): void {
        ApplySchemaProcess::run(
            payload: new ApplySchemaDTO(
                builder: $builder,
                schema: $this->schema,
                fields: $this->dto->fields,
                with: $this->dto->with,
                with_count: $this->dto->with_count,
                scopes: $this->dto->scopes,
            ),
        );
    }
}
