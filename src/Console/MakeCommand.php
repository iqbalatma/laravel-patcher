<?php

namespace Dentro\Patcher\Console;

use Illuminate\Support\Str;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand;

class MakeCommand extends MigrateMakeCommand
{
    protected $signature = 'make:patch {name : The name of the patch}';

    protected $description = 'Create a new patch file';

    /**
     * @return void
     * @throws \Exception
     */
    public function handle(): void
    {
        $name = Str::snake(trim($this->input->getArgument('name')));

        $this->writeMigration($name, null, false);

        $this->composer->dumpAutoloads();
    }

    /**
     * @param $name
     * @param $table
     * @param $create
     * @return string
     * @throws \Exception
     */
    protected function writeMigration($name, $table, $create): string
    {
        $file = $this->creator->create(
            $name, $this->getMigrationPath(), $table, $create
        );

        $this->line("<info>Created Patch:</info> $file");

        return $file;
    }

    protected function getMigrationPath(): string
    {
        return $this->laravel->basePath().DIRECTORY_SEPARATOR.'patches';
    }
}
