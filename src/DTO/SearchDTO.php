<?php

namespace Stepanenko3\LaravelApiSkeleton\DTO;

class SearchDTO extends QueryDTO
{
    public function __construct(
        // Schema Pagination
        public int $page = 1,
        public int $limit = 20,
        // Schema Rules
        public array $filters = [],
        public array $scopes = [],
        public array $sorts = [],
        public array $selects = [],
        public array $aggregates = [],
        public array $instructions = [],
        public array $includes = [],
        public array $gates = [],
    ) {
    }
}
