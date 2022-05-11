<?php

namespace SkrdIo\JobsOverview;

use Illuminate\Support\ServiceProvider;
use SkrdIo\JobsOverview\Console\NotifyCommand;

class JobsOverviewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishConfigs();
        $this->registerCommands();
    }

    public function register(): void
    {
        //
    }

    private function publishConfigs(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/jobs_overview.php',
            'jobs_overview'
        );
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([NotifyCommand::class]);
        }
    }
}
