<?php

namespace SkrdIo\JobsOverview\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use SkrdIo\JobsOverview\Notifiable;
use SkrdIo\JobsOverview\Notifications\JobsOverview;

class NotifyCommand extends Command
{
    protected $signature = 'jobs-overview:notify';

    protected $description = 'Notify an overview of jobs';

    public function handle(): int
    {
        Notification::send(new Notifiable(), new JobsOverview());

        return 0;
    }
}
