<?php

namespace Matriphe\Supervisor;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Generator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supervisor:config
        {--path=/etc/supervisord/conf.d : Supervisord config path}
        {--queue=default : Queue name}
        {--tries=3 : Number of attempts to execute}
        {--process=1 : Number of child process}
        {--timeout=60 : Timeout in seconds}
        {--production : Used in production}
        {--priority=999 : Priority value}
        {--logdir=/var/log/supervisor : Log directory}
        {--preview : Preview configuration without writing to file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Supervisor config for queue workers';

    /**
     * Filesystem handler.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $file;

    /**
     * Application handler.
     *
     * @var Illuminate\Foundation\Application
     */
    private $application;

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

        $path = rtrim($this->option('path'), '/');
        $logdir = rtrim($this->option('logdir'), '/');

        $preview = $this->option('preview');

        if (!$preview) {
            $this->checkPathWritable($path);
            $this->checkPathWritable($logdir);
        }

        extract($this->option());

        $queue = Str::slug($this->option('queue'));
        $appname = Str::slug(config('api.name'));
        $appdir = rtrim(base_path(), '/');

        $filename = implode('-', array_filter([$appname, $queue]));
        $appname = $filename;

        $worker = $this->getWorkerCommand($this->option('production'));
        $logfile = implode('/', array_filter([$logdir, $filename.'.log']));

        $search = [
            '{{appname}}', '{{queue}}', '{{worker}}', '{{tries}}', '{{process}}',
            '{{appdir}}', '{{priority}}', '{{logfile}}', '{{timeout}}',
        ];

        $replacement = compact(
            'appname', 'queue', 'worker', 'tries', 'process',
            'appdir', 'priority', 'logfile', 'timeout'
        );

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
     * Get stub.
     *
     * @return string
     */
    protected function getStub() : string
    {
        return $this->file->get(__DIR__.'/stub/supervisor.stub');
    }

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
    protected function getLaravelBaseVersion() : string
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
    protected function getProductionWorker($version) : string
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
    protected function getDevelopmentWorker($version) : string
    {
        return 'queue:listen';
    }

    /**
     * Get artisan worker command used in production based by production flag.
     *
     * @param bool $production
     *
     * @return string
     */
    protected function getWorkerCommand($production) : string
    {
        $version = $this->getLaravelBaseVersion();

        if ($production) {
            return $this->getProductionWorker($version);
        }

        return $this->getDevelopmentWorker($version);
    }
}
