<?php

namespace Matriphe\Tests\Supervisor;

use Matriphe\Supervisor\QueueGenerator;

class QueueGeneratorTest extends GeneratorTestCase
{
    protected $command = 'supervisor:queue';

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

    protected function getCommand()
    {
        return new QueueGenerator($this->file);
    }
}
