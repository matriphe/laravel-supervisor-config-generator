<?php

namespace Matriphe\Tests\Supervisor;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Matriphe\Supervisor\Generator as SupervisorConfigGenerator;
use Mockery;

class GeneratorTest extends TestCase
{
    protected $stub = '
        [program:{{appname}}-{{queue}}]
        command=php {{appdir}}/artisan {{worker}} --queue={{queue}} --tries={{tries}} --timeout={{timeout}}
        process_num={{process}}
        numprocs={{process}}
        process_name=%(process_num)s
        priority={{priority}}
        autostart=true
        autorestart=unexpected
        startretries={{tries}}
        stopsignal=QUIT
        stderr_logfile={{logfile}}';

    public function setUp()
    {
        parent::setUp();

        $this->file = Mockery::mock(Filesystem::class);
        $this->application = Mockery::mock(Application::class);
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

        $this->application->shouldReceive('version')->once()->andReturn('5.5.x');

        $command = new SupervisorConfigGenerator($this->file, $this->application);
        $this->registerCommand($command);

        $output = $this->artisan('supervisor:config');

        $this->assertSame(0, $output);
    }

    public function testCanPreviewConfigFileWithoutParameters()
    {
        $this->file->shouldReceive('isWritable')->never();
        $this->file->shouldReceive('get')->once()->andReturn($this->stub);
        $this->file->shouldReceive('put')->never();

        $this->application->shouldReceive('version')->once()->andReturn('5.5.x');

        $command = new SupervisorConfigGenerator($this->file, $this->application);
        $this->registerCommand($command);

        $output = $this->artisan('supervisor:config', ['--preview' => true]);

        $this->assertSame(0, $output);
    }

    public function testCannotCreateConfigFileBecauseOfUnwritableSupervisorDirectory()
    {
        $this->file->shouldReceive('isWritable')
            ->once()
            ->with('/etc/supervisord/conf.d')
            ->andReturn(false);

        $this->expectException('Exception');

        $command = new SupervisorConfigGenerator($this->file, $this->application);
        $this->registerCommand($command);

        $output = $this->artisan('supervisor:config');

        $this->assertSame(0, $output);
    }

    public function testCannotCreateConfigFileBecauseOfUnwritableLogDirectory()
    {
        $this->file->shouldReceive('isWritable')
            ->once()
            ->with('/etc/supervisord/conf.d')
            ->andReturn(true);

        $this->file->shouldReceive('isWritable')
            ->once()
            ->with('/var/log/supervisor')
            ->andReturn(false);

        $this->expectException('Exception');

        $command = new SupervisorConfigGenerator($this->file, $this->application);
        $this->registerCommand($command);

        $output = $this->artisan('supervisor:config');

        $this->assertSame(0, $output);
    }

    protected function registerCommand($command)
    {
        $this->app[Kernel::class]->registerCommand($command);
    }
}
