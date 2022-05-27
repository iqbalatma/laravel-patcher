<?php

namespace Dentro\Patcher;

use Dentro\Patcher\Events\PatchEnded;
use Dentro\Patcher\Events\PatchStarted;
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

        $step = $options['step'] ?? false;

        foreach ($migrations as $file) {
            $this->patch($file, $batch);

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
     * @return void
     * @throws \Throwable
     */
    protected function patch(string $file, int $batch): void
    {
        $patch = $this->resolvePath($file);

        $name = $this->getMigrationName($file);

        $this->note("<comment>Patching:</comment> $name");

        $startTime = microtime(true);

        if ($patch instanceof Patch && $this->isEligible($patch)) {
            $patch
                ->setContainer(app())
                ->setCommand(app('command.patcher'))
                ->setLogger(app('log')->driver(PatcherServiceProvider::$LOG_CHANNEL));

            $this->runPatch($patch);

            $runTime = round(microtime(true) - $startTime, 2);

            if (! $patch->isPerpetual) {
                $this->repository->log($name, $batch);
            }
            
            $perpetualMessage = $patch->isPerpetual ? " (Perpetual)" : "";

            $this->note("<info>Patched:</info> $name ($runTime seconds).<comment>$perpetualMessage</comment>");
        } else {
            $this->note("<comment>Skipped:</comment> $name is not eligible to run in current condition.");
        }
    }

    /**
     * Determine if patcher should run.
     *
     * @param \Dentro\Patcher\Patch $patch
     * @return bool
     */
    public function isEligible(Patch $patch): bool
    {
        if (method_exists($patch, 'eligible')) {
            return $patch->eligible();
        }

        return true;
    }

    /**
     * Run a migration inside a transaction if the database supports it.
     *
     * @param \Dentro\Patcher\Patch $patch
     * @return void
     * @throws \Throwable
     */
    protected function runPatch(Patch $patch): void
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

        if ($patch->withinTransaction && $this->getSchemaGrammar($connection)->supportsSchemaTransactions()) {
            $connection->transaction($callback);
            return;
        }

        $callback();
    }
}
