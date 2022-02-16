<?php

namespace SkrdIo\FailedJobSummary;

use Illuminate\Support\ServiceProvider;
use SkrdIo\FailedJobSummary\Console\NotifyCommand;

class FailedJobSummaryServiceProvider extends ServiceProvider
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

    protected function publishConfigs(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/failed_job_summary.php',
            'failed_job_summary'
        );
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([NotifyCommand::class]);
        }
    }
}
