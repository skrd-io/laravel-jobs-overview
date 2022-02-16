<?php

namespace SkrdIo\FailedJobSummary\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use SkrdIo\FailedJobSummary\Notifiable;
use SkrdIo\FailedJobSummary\Notifications\FailedJobSummary;

class NotifyCommand extends Command
{
    protected $signature = 'failed-job-summary:notify';

    protected $description = 'Notify a summary of failed jobs';

    public function handle(): int
    {
        Notification::send(new Notifiable(), new FailedJobSummary());

        return 0;
    }
}
