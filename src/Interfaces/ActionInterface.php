<?php

namespace Stepanenko3\LaravelApiSkeleton\Interfaces;

use Stepanenko3\LaravelApiSkeleton\DTO\DTO;

interface ActionInterface
{
    public function handle(DTO $dto);
}
