<?php

namespace Stepanenko3\LaravelLogicContainers\Commands;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'logic-containers:resource')]
class ContainerResourceMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'logic-containers:resource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource in logic container';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Resource';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath($this->stubsPath . '/resource.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $this->getContainerNamespace($rootNamespace) . '\\Http\\Resources';
    }
}
