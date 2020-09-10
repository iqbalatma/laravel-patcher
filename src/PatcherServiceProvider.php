<?php

namespace Jalameta\Patcher;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Jalameta\Patcher\Console\MakeCommand;
use Jalameta\Patcher\Console\PatchCommand;
use Jalameta\Patcher\Console\StatusCommand;
use Jalameta\Patcher\Console\InstallCommand;

class PatcherServiceProvider extends ServiceProvider
{
    protected $commands = [
        'PatcherPatch' => 'command.patcher',
        'PatcherInstall' => 'command.patcher.install',
        'PatcherMake' => 'command.patcher.make',
        'PatcherStatus' => 'command.patcher.status',
    ];

    /**
     * Register the patcher service.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRepository();

        $this->registerPatcher();

        $this->registerCreator();

        $this->registerCommands($this->commands);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array_merge([
            'jps.patcher', 'jps.patcher.repository', 'jps.patcher.creator',
        ], array_values($this->commands));
    }

    /**
     * Register patcher service.
     *
     * @return void
     */
    protected function registerPatcher()
    {
        $this->app->singleton('jps.patcher', function ($app) {
            $repository = $app['jps.patcher.repository'];

            return new Patcher($repository, $app['db'], $app['files'], $app['events']);
        });
    }

    /**
     * Register patcher repository.
     *
     * @return void
     */
    protected function registerRepository()
    {
        $this->app->singleton('jps.patcher.repository', function ($app) {
            return new PatcherRepository($app['db'], 'patches');
        });
    }

    /**
     * Register the migration creator.
     *
     * @return void
     */
    protected function registerCreator()
    {
        $this->app->singleton('jps.patcher.creator', function ($app) {
            return new PatcherCreator($app['files'], $app->basePath('stubs'));
        });
    }

    /**
     * Register the given commands.
     *
     * @param  array  $commands
     * @return void
     */
    protected function registerCommands(array $commands)
    {
        foreach (array_keys($commands) as $command) {
            call_user_func_array([$this, "register{$command}Command"], []);
        }

        $this->commands(array_values($commands));
    }

    /**
     * Register install command.
     *
     * @return void
     */
    protected function registerPatcherInstallCommand()
    {
        $this->app->singleton('command.patcher.install', function ($app) {
            return new InstallCommand($app['jps.patcher.repository']);
        });
    }

    /**
     * Register make command.
     *
     * @return void
     */
    protected function registerPatcherMakeCommand()
    {
        $this->app->singleton('command.patcher.make', function ($app) {
            // Once we have the migration creator registered, we will create the command
            // and inject the creator. The creator is responsible for the actual file
            // creation of the migrations, and may be extended by these developers.
            $creator = $app['jps.patcher.creator'];

            $composer = $app['composer'];

            return new MakeCommand($creator, $composer);
        });
    }

    /**
     * Register status command
     *
     * @return void
     */
    protected function registerPatcherStatusCommand()
    {
        $this->app->singleton('command.patcher.status', function ($app) {
            return new StatusCommand($app['jps.patcher']);
        });
    }

    /**
     * Register patch command.
     *
     * @return void
     */
    protected function registerPatcherPatchCommand()
    {
        $this->app->singleton('command.patcher', function (Application $app) {
            return new PatchCommand($app->make('jps.patcher'), $app->make('events'));
        });
    }
}
