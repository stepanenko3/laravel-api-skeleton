<?php

namespace Stepanenko3\LaravelLogicContainers\Commands;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'logic-containers:action')]
class ContainerActionMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'logic-containers:action';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new action in logic container';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Action';

    protected bool $addTypeToClassName = false;

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $name = $this->getNameInputWithoutType();

        if (str_ends_with($name, 'Show') || str_ends_with($name, 'Get')) {
            return $this->resolveStubPath($this->stubsPath . '/action.get.stub');
        }

        if (str_ends_with($name, 'Update')) {
            return $this->resolveStubPath($this->stubsPath . '/action.update.stub');
        }

        if (str_ends_with($name, 'Destroy') || str_ends_with($name, 'Delete')) {
            return $this->resolveStubPath($this->stubsPath . '/action.delete.stub');
        }

        if (str_ends_with($name, 'Store') || str_ends_with($name, 'Put')) {
            return $this->resolveStubPath($this->stubsPath . '/action.store.stub');
        }

        return $this->resolveStubPath($this->stubsPath . '/action.fetch.stub');
    }
}
