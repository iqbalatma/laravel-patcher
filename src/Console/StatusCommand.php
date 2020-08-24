<?php

namespace Jalameta\Patcher\Console;

use Illuminate\Database\Console\Migrations\StatusCommand as MigrationStatusCommand;

class StatusCommand extends MigrationStatusCommand
{
    protected $name = 'patcher:status';

    protected $description = 'Show the status of each patches.';
}
