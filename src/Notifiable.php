<?php

namespace SkrdIo\JobsOverview;

use Illuminate\Notifications\Notifiable as NotifiableTrait;
use Illuminate\Support\Facades\Config;

class Notifiable
{
    use NotifiableTrait;

    public function routeNotificationForSlack(): string
    {
        return Config::get('jobs_overview.slack.webhook_url');
    }
}
