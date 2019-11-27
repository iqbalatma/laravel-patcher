<?php

namespace Jalameta\Patcher\Console;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand;

class MakeCommand extends MigrateMakeCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'make:patch {name : The name of the patch}
        {--create= : The table to be created}
        {--table= : The table to migrate}
        {--path= : The location where the patch file should be created}
        {--realpath : Indicate any provided patch file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the patch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new patch file';


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
        $file = $this->creator->create(
            $name, $this->getMigrationPath(), $table, $create
        );

        if (! $this->option('fullpath')) {
            $file = pathinfo($file, PATHINFO_FILENAME);
        }

        $this->line("<info>Created Patch:</info> {$file}");
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        if (! is_null($targetPath = $this->input->getOption('path'))) {
            return ! $this->usingRealPath()
                ? $this->laravel->basePath().'/'.$targetPath
                : $targetPath;
        }

        return $this->laravel->basePath().DIRECTORY_SEPARATOR.'/patches';
    }
}