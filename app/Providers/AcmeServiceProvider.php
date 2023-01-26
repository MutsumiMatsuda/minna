<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AcmeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('acme', 'App\Utilities\Acme');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
