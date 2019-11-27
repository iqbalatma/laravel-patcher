<?php

namespace Jalameta\Patcher\Console;

use Illuminate\Database\Console\Migrations\InstallCommand as MigrationInstallCommand;

class InstallCommand extends MigrationInstallCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'patcher:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the patches repository';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->repository->setSource($this->input->getOption('database'));

        $this->repository->createRepository();

        $this->info('Patches table created successfully.');
    }
}
