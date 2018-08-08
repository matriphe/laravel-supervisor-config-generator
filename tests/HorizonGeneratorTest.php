<?php

namespace Matriphe\Tests\Supervisor;

use Matriphe\Supervisor\HorizonGenerator;

class HorizonGeneratorTest extends GeneratorTestCase
{
    protected $command = 'supervisor:horizon';

    protected $stub = '
        [program:{{appname}}]
        command={{php}} {{appdir}}/artisan horizon
        process_name=%(program_name)s
        autostart=true
        autorestart=unexpected
        startretries={{tries}}
        stopsignal=QUIT
        redirect_stderr=true
        stderr_logfile={{logfile}}';

    protected function getCommand()
    {
        return new HorizonGenerator($this->file);
    }
}
