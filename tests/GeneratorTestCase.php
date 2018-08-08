<?php

namespace Matriphe\Tests\Supervisor;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Mockery;

abstract class GeneratorTestCase extends TestCase
{
    protected $stub;

    protected $command;

    public function setUp()
    {
        parent::setUp();

        $this->file = Mockery::mock(Filesystem::class);
    }

    public function tearDown()
    {
        Mockery::close();

        parent::tearDown();
    }

    public function testCanCreateConfigFileWithoutParameters()
    {
        $this->file->shouldReceive('isWritable')->times(2)->andReturn(true);
        $this->file->shouldReceive('get')->once()->andReturn($this->stub);
        $this->file->shouldReceive('put')->once()->andReturn(true);

        $command = $this->getCommand();
        $this->registerCommand($command);

        $output = $this->artisan($this->command);

        $this->assertSame(0, $output);
    }

    public function testCanPreviewConfigFileWithoutParameters()
    {
        $this->file->shouldReceive('isWritable')->never();
        $this->file->shouldReceive('get')->once()->andReturn($this->stub);
        $this->file->shouldReceive('put')->never();

        $command = $this->getCommand();
        $this->registerCommand($command);

        $output = $this->artisan($this->command, ['--preview' => true]);

        $this->assertSame(0, $output);
    }

    public function testCannotCreateConfigFileBecauseOfUnwritableSupervisorDirectory()
    {
        $this->file->shouldReceive('isWritable')
            ->once()
            ->with('/etc/supervisor/conf.d')
            ->andReturn(false);

        $this->expectException('Exception');

        $command = $this->getCommand();
        $this->registerCommand($command);

        $output = $this->artisan($this->command);

        $this->assertSame(0, $output);
    }

    public function testCannotCreateConfigFileBecauseOfUnwritableLogDirectory()
    {
        $this->file->shouldReceive('isWritable')
            ->once()
            ->with('/etc/supervisor/conf.d')
            ->andReturn(true);

        $this->file->shouldReceive('isWritable')
            ->once()
            ->with('/var/log/supervisor')
            ->andReturn(false);

        $this->expectException('Exception');

        $command = $this->getCommand();
        $this->registerCommand($command);

        $output = $this->artisan($this->command);

        $this->assertSame(0, $output);
    }

    abstract protected function getCommand();

    protected function registerCommand($command)
    {
        $this->app[Kernel::class]->registerCommand($command);
    }
}
