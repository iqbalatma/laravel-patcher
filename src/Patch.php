<?php

namespace Dentro\Patcher;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Database\Migrations\Migration;

abstract class Patch extends Migration
{
    /**
     * The console command instance.
     *
     * @var \Illuminate\Console\Command
     */
    protected $command;

    /**
     * The container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * Logger.
     *
     * @var \Illuminate\Log\Logger
     */
    protected $logger;

    /**
     * Enables, if supported, wrapping the migration within a transaction.
     *
     * @var bool
     */
    public $withinTransaction = false;

    /**
     * Patch constructor.
     */
    public function __construct()
    {
        $this->logger = app('log')->driver(PatcherServiceProvider::$LOG_CHANNEL);
    }

    /**
     * Run patch script.
     *
     * @return void
     */
    abstract public function patch(): void;

    /**
     * Set command.
     *
     * @param \Illuminate\Console\Command $command
     * @return $this
     */
    public function setCommand(Command $command): Patch
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Set the IoC container instance.
     *
     * @param  \Illuminate\Container\Container  $container
     * @return $this
     */
    public function setContainer(Container $container): Patch
    {
        $this->container = $container;

        return $this;
    }
}
