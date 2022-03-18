<?php

namespace Dentro\Patcher\Tests\Command;

use Illuminate\Foundation\Application;
use Illuminate\Support\Composer;
use Dentro\Patcher\Console\MakeCommand;
use Dentro\Patcher\PatcherCreator;
use PHPUnit\Framework\TestCase;
use Mockery as m;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class PatcherMakeCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testBasicCreateDumpsAutoload(): void
    {
        $command = new MakeCommand(
            $creator = m::mock(PatcherCreator::class),
            $composer = m::mock(Composer::class)
        );
        $app = new Application;
        $command->setLaravel($app);
        $creator->shouldReceive('create')->once()->with('fix_foo', $app->basePath().DIRECTORY_SEPARATOR.'patches', null, false);
        $composer->shouldReceive('dumpAutoloads')->once();

        $this->runCommand($command, ['name' => 'fix_foo']);
    }

    public function testBasicCreateGivesCreatorProperArguments(): void
    {
        $command = new MakeCommand(
            $creator = m::mock(PatcherCreator::class),
            m::mock(Composer::class)->shouldIgnoreMissing()
        );
        $app = new Application;
        $command->setLaravel($app);
        $creator->shouldReceive('create')->once()->with('fix_foo', $app->basePath().DIRECTORY_SEPARATOR.'patches', null, false);

        $this->runCommand($command, ['name' => 'fix_foo']);
    }

    public function testBasicCreateGivesCreatorProperArgumentsWhenNameIsStudlyCase(): void
    {
        $command = new MakeCommand(
            $creator = m::mock(PatcherCreator::class),
            m::mock(Composer::class)->shouldIgnoreMissing()
        );
        $app = new Application;
        $command->setLaravel($app);
        $creator->shouldReceive('create')->once()->with('fix_foo', $app->basePath().DIRECTORY_SEPARATOR.'patches', null, false);

        $this->runCommand($command, ['name' => 'FixFoo']);
    }

    protected function runCommand($command, $input = [])
    {
        return $command->run(new ArrayInput($input), new NullOutput);
    }

}
