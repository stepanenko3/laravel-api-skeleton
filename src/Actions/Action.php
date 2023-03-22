<?php

namespace Stepanenko3\LaravelApiSkeleton\Actions;

use Stepanenko3\LaravelApiSkeleton\DTO\DTO;
use Stepanenko3\LaravelApiSkeleton\Interfaces\ActionInterface;

abstract class Action implements ActionInterface
{
    abstract public function handle(DTO $dto): mixed;
}
