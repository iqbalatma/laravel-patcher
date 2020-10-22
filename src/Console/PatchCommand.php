<?php

namespace Jalameta\Patcher\Console;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\SqlServerConnection;
use Illuminate\Database\Console\Migrations\MigrateCommand;

class PatchCommand extends MigrateCommand
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patcher:run {--database= : The database connection to use}
                {--force : Force the operation to run when in production}
                {--pretend : Dump the SQL queries that would be run}
                {--step : Force the patches to be run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the patches.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        $this->migrator->usingConnection($this->option('database'), function () {
            $this->prepareDatabase();

            // Next, we will check to see if a path option has been defined. If it has
            // we will use the path relative to the root of this installation folder
            // so that migrations may be run for any path within the applications.
            $this->migrator->setOutput($this->output)
                ->run($this->getMigrationPaths(), [
                    'pretend' => $this->option('pretend'),
                    'step' => $this->option('step'),
                ]);
        });

        return 0;
    }

    /**
     * Prepare the migration database for running.
     *
     * @return void
     */
    protected function prepareDatabase()
    {
        if (! $this->migrator->repositoryExists()) {
            $this->call('patcher:install', array_filter([
                '--database' => $this->option('database'),
            ]));
        }

        if (! $this->migrator->hasRunAnyMigrations() && ! $this->option('pretend')) {
            $this->loadSchemaState();
        }
    }

    /**
     * Get all of the migration paths.
     *
     * @return array
     */
    protected function getMigrationPaths()
    {
        return array_merge(
            $this->migrator->paths(), [$this->getMigrationPath()]
        );
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

    protected function loadSchemaState()
    {
        $connection = $this->migrator->resolveConnection($this->option('database'));

        // First, we will make sure that the connection supports schema loading and that
        // the schema file exists before we proceed any further. If not, we will just
        // continue with the standard migration operation as normal without errors.
        if ($connection instanceof SQLiteConnection ||
            $connection instanceof SqlServerConnection ||
            ! is_file($path = $this->schemaPath($connection))) {
            return;
        }

        $this->line('<info>Loading stored patches:</info> '.$path);

        $startTime = microtime(true);

        // Since the schema file will create the "migrations" table and reload it to its
        // proper state, we need to delete it here so we don't get an error that this
        // table already exists when the stored database schema file gets executed.
        $this->migrator->deleteRepository();

        $connection->getSchemaState()->handleOutputUsing(function ($type, $buffer) {
            $this->output->write($buffer);
        })->load($path);

        $runTime = number_format((microtime(true) - $startTime) * 1000, 2);

        $this->line('<info>Loaded stored patches.</info> ('.$runTime.'ms)');
    }
}
