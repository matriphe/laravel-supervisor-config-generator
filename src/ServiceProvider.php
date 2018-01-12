<?php

namespace Matriphe\Supervisor;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->commands([
            Generator::class,
        ]);
    }
}
