<?php

namespace Stepanenko3\LaravelApiSkeleton\DTO;

abstract class QueryDTO extends DTO
{
    public function __construct(
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
