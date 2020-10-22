<?php

namespace Jalameta\Patcher\Tests\Command;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Jalameta\Patcher\Console\PatchCommand;
use Jalameta\Patcher\Patcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Mockery as m;

class PatcherRunCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testBasicPatchesCallMigratorWithProperArguments()
    {
        $command = new PatchCommand($migrator = m::mock(Patcher::class), $dispatcher = m::mock(Dispatcher::class));
        $app = new ApplicationDatabaseMigrationStub();
        $command->setLaravel($app);
        $migrator->shouldReceive('paths')->once()->andReturn([]);
        $migrator->shouldReceive('hasRunAnyMigrations')->andReturn(true);
        $migrator->shouldReceive('usingConnection')->once()->andReturnUsing(function ($name, $callback) {
            return $callback();
        });
        $migrator->shouldReceive('setOutput')->once()->andReturn($migrator);
        $migrator->shouldReceive('run')->once()->with([$app->basePath().DIRECTORY_SEPARATOR.'patches'], ['pretend' => false, 'step' => false]);
        $migrator->shouldReceive('getNotes')->andReturn([]);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);

        $this->runCommand($command);
    }

    public function testPatchesRepositoryCreatedWhenNecessary()
    {
        $params = [$migrator = m::mock(Patcher::class), $dispatcher = m::mock(Dispatcher::class)];
        $command = $this->getMockBuilder(PatchCommand::class)->onlyMethods(['call'])->setConstructorArgs($params)->getMock();
        $app = new ApplicationDatabaseMigrationStub();
        $command->setLaravel($app);
        $migrator->shouldReceive('paths')->once()->andReturn([]);
        $migrator->shouldReceive('hasRunAnyMigrations')->andReturn(true);
        $migrator->shouldReceive('usingConnection')->once()->andReturnUsing(function ($name, $callback) {
            return $callback();
        });
        $migrator->shouldReceive('setOutput')->once()->andReturn($migrator);
        $migrator->shouldReceive('run')->once()->with([$app->basePath().DIRECTORY_SEPARATOR.'patches'], ['pretend' => false, 'step' => false]);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(false);
        $command->expects($this->once())->method('call')->with($this->equalTo('patcher:install'), $this->equalTo([]));

        $this->runCommand($command);
    }

    public function testTheCommandMayBePretended()
    {
        $command = new PatchCommand($migrator = m::mock(Patcher::class), $dispatcher = m::mock(Dispatcher::class));
        $app = new ApplicationDatabaseMigrationStub(['path.database' => __DIR__]);
        $app->useDatabasePath(__DIR__);
        $command->setLaravel($app);
        $migrator->shouldReceive('paths')->once()->andReturn([]);
        $migrator->shouldReceive('hasRunAnyMigrations')->andReturn(true);
        $migrator->shouldReceive('usingConnection')->once()->andReturnUsing(function ($name, $callback) {
            return $callback();
        });
        $migrator->shouldReceive('setOutput')->once()->andReturn($migrator);
        $migrator->shouldReceive('run')->once()->with([$app->basePath().DIRECTORY_SEPARATOR.'patches'], ['pretend' => true, 'step' => false]);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);

        $this->runCommand($command, ['--pretend' => true]);
    }

    public function testTheDatabaseMayBeSet()
    {
        $command = new PatchCommand($migrator = m::mock(Patcher::class), $dispatcher = m::mock(Dispatcher::class));
        $app = new ApplicationDatabaseMigrationStub();
        $command->setLaravel($app);
        $migrator->shouldReceive('paths')->once()->andReturn([]);
        $migrator->shouldReceive('hasRunAnyMigrations')->andReturn(true);
        $migrator->shouldReceive('usingConnection')->once()->andReturnUsing(function ($name, $callback) {
            return $callback();
        });
        $migrator->shouldReceive('setOutput')->once()->andReturn($migrator);
        $migrator->shouldReceive('run')->once()->with([$app->basePath().DIRECTORY_SEPARATOR.'patches'], ['pretend' => false, 'step' => false]);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);

        $this->runCommand($command, ['--database' => 'foo']);
    }

    public function testStepMayBeSet()
    {
        $command = new PatchCommand($migrator = m::mock(Patcher::class), $dispatcher = m::mock(Dispatcher::class));
        $app = new ApplicationDatabaseMigrationStub();
        $command->setLaravel($app);
        $migrator->shouldReceive('paths')->once()->andReturn([]);
        $migrator->shouldReceive('hasRunAnyMigrations')->andReturn(true);
        $migrator->shouldReceive('usingConnection')->once()->andReturnUsing(function ($name, $callback) {
            return $callback();
        });
        $migrator->shouldReceive('setOutput')->once()->andReturn($migrator);
        $migrator->shouldReceive('run')->once()->with([$app->basePath().DIRECTORY_SEPARATOR.'patches'], ['pretend' => false, 'step' => true]);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);

        $this->runCommand($command, ['--step' => true]);
    }

    protected function runCommand($command, $input = [])
    {
        return $command->run(new ArrayInput($input), new NullOutput);
    }
}

class ApplicationDatabaseMigrationStub extends Application
{
    public function __construct(array $data = [])
    {
        foreach ($data as $abstract => $instance) {
            $this->instance($abstract, $instance);
        }
    }

    public function environment(...$environments)
    {
        return 'development';
    }
}
