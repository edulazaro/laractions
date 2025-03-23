<?php

namespace EduLazaro\Laractions;

use Illuminate\Support\ServiceProvider;
use EduLazaro\Laractions\Console\Commands\MakeActionCommand;
use EduLazaro\Laractions\Console\Commands\ListActionsCommand;

class LaractionsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([MakeActionCommand::class]);
            $this->commands([ListActionsCommand::class]);

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'laractions-migrations');
        }
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}