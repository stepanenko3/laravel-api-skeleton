<?php

namespace Stepanenko3\LaravelLogicContainers\Actions;

use Stepanenko3\LaravelLogicContainers\DTO\DTO;
use Stepanenko3\LaravelLogicContainers\Interfaces\ActionInterface;

abstract class Action implements ActionInterface
{
    abstract public function handle(DTO $dto): mixed;
}
