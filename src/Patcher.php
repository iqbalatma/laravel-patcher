<?php

namespace Dentro\Patcher;

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
        $migration = $this->resolve(
            $name = $this->getMigrationName($file)
        );

        $migration->setContainer(app())->setCommand(app('command.patcher'));

        if ($pretend) {
            $this->pretendToRun($migration, 'patch');

            return;
        }

        $this->note("<comment>Patching:</comment> {$name}");

        $startTime = microtime(true);

        $this->runPatch($migration, 'patch');

        $runTime = round(microtime(true) - $startTime, 2);

        $this->repository->log($name, $batch);

        $this->note("<info>Patched:</info>  {$name} ({$runTime} seconds)");
    }

    /**
     * Run a migration inside a transaction if the database supports it.
     *
     * @param object $migration
     * @param string $method
     *
     * @return void
     * @throws \Throwable
     */
    protected function runPatch($migration, string $method): void
    {
        $connection = $this->resolveConnection(
            $migration->getConnection()
        );

        $callback = static function () use ($migration, $method) {
            if (method_exists($migration, $method)) {
                $migration->{$method}();
            }
        };

        $this->getSchemaGrammar($connection)->supportsSchemaTransactions()
        && $migration->withinTransaction
            ? $connection->transaction($callback)
            : $callback();
    }
}
