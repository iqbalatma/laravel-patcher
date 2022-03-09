<?php

namespace Dentro\Patcher\Console;

use Illuminate\Database\Console\Migrations\InstallCommand as MigrationInstallCommand;

class InstallCommand extends MigrationInstallCommand
{
    protected $name = 'patcher:install';

    protected $description = 'Create the patches repository';

    public function handle(): void
    {
        $this->repository->setSource($this->input->getOption('database'));

        $this->repository->createRepository();

        $this->info('Patches table created successfully.');
    }
}
