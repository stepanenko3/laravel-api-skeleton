<?php

namespace Stepanenko3\LaravelLogicContainers\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeLogicContainer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:logic-container
        {?name: The name of the logic container class}
        {--dto: Create DTOs or no}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make logic container';

    protected Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        dd(11);
        $name = trim($this->input->getArgument('name'));
        $dto = trim($this->input->getOption('dto'));

        if (!$name) {
            $name = $this->ask('Как вы хотите назвать Logic Container?');
        }

        if ($this->confirm('Do you wish to continue?', true)) {
            // ...
        }
    }

    /**
     * Return the stub file path.
     *
     * @return string
     */
    public function getStubPath()
    {
        return __DIR__ . '/../../../stubs/interface.stub';
    }
}
