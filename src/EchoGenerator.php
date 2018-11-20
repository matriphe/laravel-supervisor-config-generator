<?php

namespace Matriphe\Supervisor;

use Symfony\Component\Console\Input\InputOption;

class EchoGenerator extends Generator
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'supervisor:echo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Supervisor config for Laravel Echo Server';

    /**
     * The default queue identifier.
     *
     * @var string
     */
    protected $identifier = 'laravel-echo-server';

    /**
     * Get stub.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->file->get(__DIR__.'/stub/echo.stub');
    }

    /**
     * Get artisan worker command used in production based by Laravel base version.
     *
     * @param string $version
     *
     * @return string
     */
    protected function getProductionWorker($version)
    {
        return $this->getDevelopmentWorker($version);
    }

    /**
     * Get artisan worker command used in development based by Laravel base version.
     *
     * @param string $version
     *
     * @return string
     */
    protected function getDevelopmentWorker($version)
    {
        return '';
    }

    /**
     * Get search keywords.
     *
     * @return array
     */
    protected function getSearches()
    {
        return [
            '{{node}}', '{{echo}}', '{{appname}}', '{{queue}}', '{{worker}}',
            '{{tries}}', '{{process}}', '{{appdir}}', '{{priority}}',
            '{{logfile}}', '{{timeout}}',
        ];
    }

    /**
     * Get replacement variables.
     *
     * @param array $options
     *
     * @return array
     */
    protected function getReplacements($options = [])
    {
        extract($options);

        return compact(
            'node', 'echo', 'appname', 'queue', 'worker', 'tries', 'process',
            'appdir', 'priority', 'logfile', 'timeout'
        );
    }

    /**
     * Get the additional console command options.
     *
     * @return array
     */
    protected function getAdditionalOptions()
    {
        return [
            [
                'node',
                null,
                InputOption::VALUE_REQUIRED,
                'Node binary path',
                '/usr/bin/node',
            ],
            [
                'echo',
                null,
                InputOption::VALUE_REQUIRED,
                'Laravel Echo Server path',
                '/usr/local/bin/laravel-echo-server',
            ],
        ];
    }
}
