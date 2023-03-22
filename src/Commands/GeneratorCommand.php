<?php

namespace Stepanenko3\LaravelApiSkeleton\Commands;

use Illuminate\Console\GeneratorCommand as BaseGeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

abstract class GeneratorCommand extends BaseGeneratorCommand
{
    protected string $stubsPath = '/stubs';

    protected bool $addTypeToClassName = true;

    protected bool $pluralTypeToClassName = true;

    protected array $methods = ['show', 'get', 'update', 'destroy', 'delete', 'store', 'put', 'fetch'];

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param string $stub
     *
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath($this->stubsPath . '/' . mb_strtolower($this->type) . '.stub');
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        $name = trim($this->argument('name'));

        if ($this->addTypeToClassName && !Str::endsWith($name, $this->type)) {
            $name .= ucwords($this->type);
        }

        return $name;
    }

    /**
     * Get the desired class name from the input.
     */
    protected function getNameInputWithoutType(): string
    {
        return Str::beforeLast(
            $this->getNameInput(),
            ucwords($this->type),
        );
    }

    /**
     * Get the desired class name from the input.
     */
    protected function getNameInputWithoutMethod(): string
    {
        $str = $this->getNameInputWithoutType();

        return str_ireplace(
            $this->methods,
            '',
            $str,
        );
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getContainerInput()
    {
        return trim($this->argument('container')) ?: $this->getNameInputWithoutMethod();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the ' . strtolower($this->type)],
            ['container', InputArgument::OPTIONAL, 'The name of the container'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the ' . mb_strtolower($this->type) . ' already exists'],
        ];
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getContainerNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Services\\' . ucwords($this->getContainerInput());
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
        $type = $this->pluralTypeToClassName
            ? Str::plural($this->type, 2)
            : Str::singular($this->type);

        return $this->getContainerNamespace($rootNamespace) . '\\' . $type;
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this
            ->replaceNamespace($stub, $name)
            ->replaceContainer($stub)
            ->replaceClass($stub, $name);
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceContainer(&$stub)
    {
        $stub = str_replace(
            ['DummyContainer', '{{ container }}', '{{container}}'],
            $this->getContainerInput(),
            $stub,
        );

        return $this;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);
        $resourceName = $this->getNameInputWithoutMethod();
        $singularName = Str::singular($resourceName);
        $pluralName = Str::plural($resourceName);

        $searches = [
            ['DummyClass', 'DummySingularName', 'DummyPluralName'],
            ['{{ class }}', '{{ singularName }}', '{{ pluralName }}'],
            ['{{class}}', '{{singularName}}', '{{pluralName}}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$class, $singularName, $pluralName],
                $stub,
            );
        }

        return $stub;
    }
}
