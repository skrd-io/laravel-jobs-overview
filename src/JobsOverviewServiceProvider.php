<?php

namespace SkrdIo\JobsOverview;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use SkrdIo\JobsOverview\Console\NotifyCommand;
use SkrdIo\JobsOverview\Models\JobConclusion;

class JobsOverviewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishConfigs();
        $this->registerCommands();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->logJobConclusions();
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

    private function logJobConclusions(): void
    {
        Queue::after(function (JobProcessed $event) {
            JobConclusion::create([
                'type' => $event->job->resolveName(),
                'is_fail' => false,
            ]);
        });

        Queue::failing(function (JobFailed $event) {
            JobConclusion::create([
                'type' => $event->job->resolveName(),
                'is_fail' => true,
            ]);
        });
    }
}
