<?php

namespace Matriphe\Supervisor;

class HorizonGenerator extends Generator
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'supervisor:horizon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Supervisor config for Laravel Horizon';

    /**
     * The default queue identifier.
     *
     * @var string
     */
    protected $identifier = 'laravel-horizon';

    /**
     * Get stub.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->file->get(__DIR__.'/stub/horizon.stub');
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
        return 'horizon';
    }
}
