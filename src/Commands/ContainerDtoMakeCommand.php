<?php

namespace Stepanenko3\LaravelApiSkeleton\Commands;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'logic-containers:dto')]
class ContainerDtoMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'logic-containers:dto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new dto in logic container';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'DTO';

    protected bool $pluralTypeToClassName = false;

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $name = $this->getNameInputWithoutType();

        if (str_ends_with($name, 'Show') || str_ends_with($name, 'Get')) {
            return $this->resolveStubPath($this->stubsPath . '/dto.get.stub');
        }

        if (str_ends_with($name, 'Update')) {
            return $this->resolveStubPath($this->stubsPath . '/dto.update.stub');
        }

        if (str_ends_with($name, 'Destroy') || str_ends_with($name, 'Delete')) {
            return $this->resolveStubPath($this->stubsPath . '/dto.delete.stub');
        }

        if (str_ends_with($name, 'Store') || str_ends_with($name, 'Put')) {
            return $this->resolveStubPath($this->stubsPath . '/dto.store.stub');
        }

        return $this->resolveStubPath($this->stubsPath . '/dto.fetch.stub');
    }
}
