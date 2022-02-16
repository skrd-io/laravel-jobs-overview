<?php

namespace SkrdIo\FailedJobSummary;

use Illuminate\Notifications\Notifiable as NotifiableTrait;
use Illuminate\Support\Facades\Config;

class Notifiable
{
    use NotifiableTrait;

    public function routeNotificationForSlack(): string
    {
        return Config::get('failed_job_summary.slack.webhook_url');
    }
}
