<?php

namespace Stepanenko3\LaravelLogicContainers\Interfaces;

use Stepanenko3\LaravelLogicContainers\DTO\DTO;

interface ActionInterface
{
    public function handle(DTO $dto);
}
