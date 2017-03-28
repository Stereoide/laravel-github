<?php

namespace Stereoide\Github;

use Illuminate\Support\ServiceProvider;

class GithubServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        /* Config file */

        $this->publishes([
            __DIR__ . '/config/github.php' => config_path('github.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        \App::bind('github', function()
        {
            return new \Stereoide\Github\GithubController;
        });
    }
}
