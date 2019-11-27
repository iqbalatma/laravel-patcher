<?php

namespace Jalameta\Patcher;

use Illuminate\Database\Migrations\Migration;

abstract class Patch extends Migration
{
    /**
     * Run patch script.
     *
     * @return void
     */
    abstract public function patch();
}
