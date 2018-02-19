<?php

namespace Matriphe\Supervisor;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

abstract class Generator extends Command
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'supervisor:generator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Supervisor config for queue workers';

    /**
     * The default queue name.
     *
     * @var string
     */
    protected $queue = 'default';

    /**
     * The default queue identifier.
     *
     * @var string
     */
    protected $identifier;

    /**
     * Filesystem handler.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $file;

    /**
     * Application handler.
     *
     * @var Illuminate\Foundation\Application
     */
    protected $application;

    /**
     * Application worker name.
     *
     * @var Illuminate\Foundation\Application
     */
    protected $appname;

    /**
     * Create a new command instance.
     */
    public function __construct(Filesystem $file, Application $application)
    {
        parent::__construct();

        $this->file = $file;
        $this->application = $application;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info($this->description);

        $appdir = rtrim(base_path(), '/');

        $path = rtrim($this->option('path'), '/');
        $logdir = rtrim($this->option('logdir'), '/');

        $filename = $this->getFilename();
        $appname = $filename;

        $preview = $this->option('preview');

        if (!$preview) {
            $this->checkPathWritable($path);
            $this->checkPathWritable($logdir);
        }

        $worker = $this->getWorkerCommand($this->option('production'));
        $logfile = implode('/', array_filter([$logdir, $filename.'.log']));

        $search = $this->getSearches();

        $options = array_merge(
            $this->option(), compact('appname', 'worker', 'logfile', 'appdir')
        );

        $replacement = $this->getReplacements($options);

        $file = $this->getStub();
        $content = str_replace($search, $replacement, $file);

        $filepath = implode('/', array_filter([$path, $filename.'.conf']));

        if ($preview) {
            $this->line('');
            $this->line('<fg=cyan>'.$content.'</>');
            $this->line('');

            return $this->comment('File will be saved to <fg=yellow>'.$filepath.'</>');
        }

        $this->file->put($filepath, $content);

        return $this->comment('Config file saved to <fg=yellow>'.$filepath.'</>');
    }

    /**
     * Get file name.
     *
     * @return string
     */
    protected function getFilename()
    {
        return Str::slug(implode(' ', array_filter([
            config('app.name'), $this->identifier, $this->option('queue'),
        ])), '_');
    }

    /**
     * Get search keywords.
     *
     * @return array
     */
    protected function getSearches()
    {
        return [
            '{{php}}', '{{appname}}', '{{queue}}', '{{worker}}', '{{tries}}',
            '{{process}}', '{{appdir}}', '{{priority}}', '{{logfile}}',
            '{{timeout}}',
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
            'php', 'appname', 'queue', 'worker', 'tries', 'process', 'appdir',
            'priority', 'logfile', 'timeout'
        );
    }

    /**
     * Get stub.
     *
     * @return string
     */
    abstract protected function getStub();

    /**
     * Check if path writable.
     *
     * @param mixed $path
     */
    protected function checkPathWritable($path)
    {
        if (!$this->file->isWritable($path)) {
            throw new Exception('Make sure you has permission to write to `'.$path.'`');
        }
    }

    /**
     * Get Laravel base version.
     *
     * @return string
     */
    protected function getLaravelBaseVersion()
    {
        return substr($this->application->version(), 0, 3);
    }

    /**
     * Get artisan worker command used in production based by Laravel base version.
     *
     * @param string $version
     *
     * @return string
     */
    abstract protected function getProductionWorker($version);

    /**
     * Get artisan worker command used in development based by Laravel base version.
     *
     * @param string $version
     *
     * @return string
     */
    abstract protected function getDevelopmentWorker($version);

    /**
     * Get artisan worker command used in production based by production flag.
     *
     * @param bool $production
     *
     * @return string
     */
    protected function getWorkerCommand($production)
    {
        $version = $this->getLaravelBaseVersion();

        if ($production) {
            return $this->getProductionWorker($version);
        }

        return $this->getDevelopmentWorker($version);
    }

    /**
     * Get the base console command options.
     *
     * @return array
     */
    protected function getBaseOptions()
    {
        return [
            ['production', null, InputOption::VALUE_NONE, 'Used in production', null],
            ['preview', null, InputOption::VALUE_NONE, 'Preview configuration without writing to file', null],

            ['queue', null, InputOption::VALUE_REQUIRED, 'Queue name', $this->queue],
            ['path', null, InputOption::VALUE_REQUIRED, 'Supervisord config path', '/etc/supervisor/conf.d'],
            ['tries', null, InputOption::VALUE_REQUIRED, 'Number of attempts to execute', 3],
            ['process', null, InputOption::VALUE_REQUIRED, 'Number of child process', 1],
            ['timeout', null, InputOption::VALUE_REQUIRED, 'Process timeout in seconds', 60],
            ['priority', null, InputOption::VALUE_REQUIRED, 'Priority value', 999],
            ['logdir', null, InputOption::VALUE_REQUIRED, 'Log directory', '/var/log/supervisor'],
        ];
    }

    /**
     * Get the additional console command options.
     *
     * @return array
     */
    protected function getAdditionalOptions()
    {
        return [
            ['php', null, InputOption::VALUE_REQUIRED, 'PHP binary path', '/usr/bin/php'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(
            $this->getAdditionalOptions(), $this->getBaseOptions()
        );
    }
}
