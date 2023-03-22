<?php

namespace Stepanenko3\LaravelApiSkeleton\Commands;

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

    public function __construct(protected Filesystem $filesystem)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        dd(11);
        $name = trim((string) $this->input->getArgument('name'));
        trim((string) $this->input->getOption('dto'));

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
