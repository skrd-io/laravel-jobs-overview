<?php

namespace SkrdIo\FailedJobSummary\Notifications;

use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class FailedJobSummary extends Notification
{
    public function via(): array
    {
        return ['slack'];
    }

    public function toSlack(): SlackMessage
    {
        $failedJobs = [
            "App\\Notifications\\BookingCompletedByAgent\t71 (+7000%)",
            "App\\Jobs\\RemoveExpiredBookings\t51 (+75.86%)",
            "App\\Jobs\\RemoveConflictingBookingSlots\t2 (-97.44%)"
        ];

        return (new SlackMessage())
            ->from('Failed Job Summary', ':mild-panic:')
            ->content("*Past 24 hours*\n\n" . implode("\n", $failedJobs) . "\n")
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->image('https://dummyimage.com/1165x720/0f172a/f8fafc.png%26text=Line+graph+of+failed+jobs+over+the+last+30+days');
            });
    }
}
