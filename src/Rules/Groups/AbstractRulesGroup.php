<?php

namespace Stepanenko3\LaravelApiSkeleton\Rules\Groups;

use Stepanenko3\LaravelApiSkeleton\Traits\Makeable;

abstract class AbstractRulesGroup
{
    use Makeable;

    abstract public function toArray(): array;
}
