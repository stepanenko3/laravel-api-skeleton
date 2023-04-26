<?php

namespace Stepanenko3\LaravelApiSkeleton\Contracts;

interface ActionContract
{
    public function handle(
        DtoContract $dto,
    ): mixed;
}
