<?php

namespace Jalameta\Patcher\Console;

use Illuminate\Support\Str;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand;

class MakeCommand extends MigrateMakeCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'make:patch {name : The name of the patch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new patch file';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        // It's possible for the developer to specify the tables to modify in this
        // schema operation. The developer may also specify if this table needs
        // to be freshly created so we can create the appropriate migrations.
        $name = Str::snake(trim($this->input->getArgument('name')));

        // Now we are ready to write the migration out to disk. Once we've written
        // the migration out, we will dump-autoload for the entire framework to
        // make sure that the migrations are registered by the class loaders.
        $this->writeMigration($name, 'patches', false);

        $this->composer->dumpAutoloads();
    }

    /**
     * Write the migration file to disk.
     *
     * @param string $name
     * @param string $table
     * @param bool   $create
     *
     * @return string
     * @throws \Exception
     */
    protected function writeMigration($name, $table, $create)
    {
        if (! $this->creator->getFilesystem()->isDirectory($this->getMigrationPath())) {
            $this->creator->getFilesystem()->makeDirectory($this->getMigrationPath(), 0755, true);
        }

        $file = $this->creator->create(
            $name, $this->getMigrationPath(), $table, $create
        );

        $this->line("<info>Created Patch:</info> {$file}");
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
