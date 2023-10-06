<?php

namespace Stepanenko3\LaravelApiSkeleton\DTO;

use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilderContract;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

class ApplySchemaDTO extends SchemaDTO
{
    public function __construct(
        public EloquentBuilderContract | QueryBuilderContract | Builder $builder,
        public Schema $schema,
        public array $fields,
        public array $with,
        public array $with_count,
        public bool $isAuthorizingEnabled = false,
    ) {
    }
}
