<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class UtlServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('utl', 'App\Utilities\Utl');
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
