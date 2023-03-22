<?php

namespace Stepanenko3\LaravelApiSkeleton\Commands;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'logic-containers:schema')]
class ContainerSchemaMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'logic-containers:schema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new http schema in logic container';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Schema';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath($this->stubsPath . '/schema.stub');
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
        return $this->getContainerNamespace($rootNamespace) . '\\Http\\Schemas';
    }
}
