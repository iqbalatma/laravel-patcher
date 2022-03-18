<?php

namespace Dentro\Patcher\Tests\Command;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Foundation\Application;
use Dentro\Patcher\Console\InstallCommand;
use PHPUnit\Framework\TestCase;
use Mockery as m;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class PatcherInstallCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testFireCallsRepositoryToInstall(): void
    {
        $command = new InstallCommand($repo = m::mock(MigrationRepositoryInterface::class));

        $command->setLaravel(new Application());
        $repo->shouldReceive('setSource')->once()->with('foo');
        $repo->shouldReceive('createRepository')->once();

        $this->runCommand($command, ['--database' => 'foo']);
    }

    protected function runCommand(Command $command, $options = []): int
    {
        return $command->run(new ArrayInput($options), new NullOutput());
    }
}
