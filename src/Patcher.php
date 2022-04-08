<?php

namespace Dentro\Patcher;

use Dentro\Patcher\Events\PatchEnded;
use Dentro\Patcher\Events\PatchStarted;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Migrations\Migrator;

class Patcher extends Migrator
{
    /**
     * Run an array of migrations.
     *
     * @param array $migrations
     * @param array $options
     *
     * @return void
     * @throws \Throwable
     */
    public function runPending(array $migrations, array $options = []): void
    {
        if (count($migrations) === 0) {
            $this->note('<info>Nothing to patch.</info>');

            return;
        }

        $batch = $this->repository->getNextBatchNumber();

        $pretend = $options['pretend'] ?? false;

        $step = $options['step'] ?? false;

        foreach ($migrations as $file) {
            $this->patch($file, $batch, $pretend);

            if ($step) {
                $batch++;
            }
        }
    }

    /**
     * Run "patch" a migration instance.
     *
     * @param string $file
     * @param int $batch
     * @param bool $pretend
     *
     * @return void
     * @throws \Throwable
     */
    protected function patch(string $file, int $batch, bool $pretend): void
    {
        $migration = $this->resolvePath($file);

        $name = $this->getMigrationName($file);

        $migration->setContainer(app())->setCommand(app('command.patcher'));

        $this->note("<comment>Patching:</comment> {$name}");

        $startTime = microtime(true);

        if (method_exists($migration, 'eligible') && $migration->eligible())
        {
            $this->runPatch($migration);
        }

        $runTime = round(microtime(true) - $startTime, 2);

        $this->repository->log($name, $batch);

        $this->note("<info>Patched:</info>  {$name} ({$runTime} seconds)");
    }

    /**
     * Determine if patcher should run.
     *
     * @param \Illuminate\Database\Migrations\Migration $migration
     * @return bool
     */
    public function isEligible(Migration $migration): bool
    {
        if (method_exists($migration, 'eligible')) {
            return $migration->eligible();
        }

        return true;
    }

    /**
     * Run a migration inside a transaction if the database supports it.
     *
     * @param object $patch
     * @return void
     * @throws \Throwable
     */
    protected function runPatch(object $patch): void
    {
        $connection = $this->resolveConnection(
            $patch->getConnection()
        );

        $dispatchEvent = function (object $event) {
            $this->events->dispatch($event);
        };

        $callback = static function () use ($patch, $dispatchEvent) {
            if (method_exists($patch, 'patch')) {
                if ($patch instanceof Patch) {
                    $dispatchEvent(new PatchStarted($patch));
                }

                $patch->patch();

                if ($patch instanceof Patch) {
                    $dispatchEvent(new PatchEnded($patch));
                }
            }
        };

        $this->getSchemaGrammar($connection)->supportsSchemaTransactions()
        && $patch->withinTransaction
            ? $connection->transaction($callback)
            : $callback();
    }
}
