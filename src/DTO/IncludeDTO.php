<?php

namespace Stepanenko3\LaravelApiSkeleton\DTO;

class IncludeDTO extends QueryDTO
{
    public function __construct(
        public string $relation,
        // Schema Rules
        public array $filters = [],
        public array $scopes = [],
        public array $sorts = [],
        public array $selects = [],
        public array $aggregates = [],
        public array $instructions = [],
        public array $includes = [],
    ) {
    }
}
