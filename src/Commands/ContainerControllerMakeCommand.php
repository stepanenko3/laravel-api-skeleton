<?php

namespace Stepanenko3\LaravelLogicContainers\Commands;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'logic-containers:controller')]
class ContainerControllerMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'logic-containers:controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new controller in logic container';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';
}
