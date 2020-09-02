<?php

namespace Jalameta\Patcher\Console;

use Illuminate\Database\Console\Migrations\StatusCommand as MigrationStatusCommand;

class StatusCommand extends MigrationStatusCommand
{
    protected $name = 'patcher:status';

    protected $description = 'Show the status of each patches.';

    public function handle()
    {
        return $this->migrator->usingConnection($this->option('database'), function () {
            if (! $this->migrator->repositoryExists()) {
                $this->error('Patcher table not found.');

                return 1;
            }

            $ran = $this->migrator->getRepository()->getRan();

            $batches = $this->migrator->getRepository()->getMigrationBatches();

            if (count($patches = $this->getStatusFor($ran, $batches)) > 0) {
                $this->table(['Ran?', 'Patch', 'Batch'], $patches);
            } else {
                $this->error('No patch found');
            }
        });
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        return $this->laravel->basePath().DIRECTORY_SEPARATOR.'patches';
    }
}
