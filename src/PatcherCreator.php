<?php

namespace Dentro\Patcher;

use Illuminate\Database\Migrations\MigrationCreator;

class PatcherCreator extends MigrationCreator
{
    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function stubPath(): string
    {
        return __DIR__.'/../stubs';
    }

    /**
     * Get the migration stub file.
     *
     * @param string|null $table
     * @param bool $create
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getStub($table, $create): string
    {
        return $this->files->get($this->files->exists($customPath = $this->customStubPath.'/blank.stub')
            ? $customPath
            : $this->stubPath().'/blank.stub');
    }
}
