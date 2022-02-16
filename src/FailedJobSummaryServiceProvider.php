<?php

namespace SkrdIo\FailedJobSummary;

use Illuminate\Support\ServiceProvider;

class FailedJobSummaryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishConfigs();
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
}
