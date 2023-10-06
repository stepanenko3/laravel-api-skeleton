<?php

namespace Stepanenko3\LaravelApiSkeleton\DTO;

class SchemaDTO extends DTO
{
    public function __construct(
        public array $fields,
        public array $with,
        public array $with_count,
        public array $scopes,
    ) {
    }

    public function with(
        array $relations = []
    ): self {
        $this->with = array_merge(
            $this->with,
            $relations,
        );

        return $this;
    }

    public function withCount(
        array $relations = []
    ): self {
        $this->with_count = array_merge(
            $this->with_count,
            $relations,
        );

        return $this;
    }
}
