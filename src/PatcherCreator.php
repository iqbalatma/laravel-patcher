<?php

namespace Jalameta\Patcher;

use Illuminate\Database\Migrations\MigrationCreator;

class PatcherCreator extends MigrationCreator
{
    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function stubPath()
    {
        return __DIR__.'/../stubs';
    }
}
