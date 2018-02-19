<?php

namespace Matriphe\Supervisor;

class QueueGenerator extends Generator
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'supervisor:queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Supervisor config for queue workers';

    /**
     * The default queue identifier.
     *
     * @var string
     */
    protected $identifier = 'queue-worker';

    /**
     * Get stub.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->file->get(__DIR__.'/stub/queue.stub');
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
        if (in_array($version, ['5.0', '5.1', '5.2'])) {
            return 'queue:work --daemon';
        }

        return 'queue:work';
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
        return 'queue:listen';
    }
}
