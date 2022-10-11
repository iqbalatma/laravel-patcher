<?php

namespace Dentro\Patcher;

use Dentro\Patcher\Events\PatchEnded;
use Dentro\Patcher\Events\PatchStarted;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\Task;
use Illuminate\Console\View\Components\Warn;
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
            $this->write(Info::class, 'Nothing to patch.');

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

        $this->write(Info::class, "Patching: $name");

        if ($patch instanceof Patch && $this->isEligible($patch)) {
            $perpetualMessage = $patch->isPerpetual ? " (Perpetual)" : "";

            $patch
                ->setContainer(app())
                ->setCommand(app('command.patcher'))
                ->setLogger(app('log')->driver(PatcherServiceProvider::$LOG_CHANNEL));

            $action = function () use ($patch, $batch, $name) {
                $this->runPatch($patch);

                if (! $patch->isPerpetual) {
                    $this->repository->log($name, $batch);
                }
            };

            /** @noinspection PhpParamsInspection */
            $this->write(Task::class, $name.$perpetualMessage, $action);
        } else {
            $this->write(Warn::class, "Skipped: $name is not eligible to run in current condition.");
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
