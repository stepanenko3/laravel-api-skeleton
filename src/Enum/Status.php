<?php

namespace Stepanenko3\LaravelApiSkeleton\Enum;

class Status extends Enum
{
    public const DRAFT = 0;

    public const ACTIVE = 1;

    public const REJECTED = 2;

    public const POSTPONED = 3;
}
