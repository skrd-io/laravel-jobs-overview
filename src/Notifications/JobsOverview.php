<?php

namespace SkrdIo\JobsOverview\Notifications;

use Carbon\Carbon;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class JobsOverview extends Notification
{
    public function via(): array
    {
        return ['slack'];
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage())
            ->from('Failed Job Summary', ':mild-panic:')
            ->content($this->generateSummary())
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->image($this->generateLineGraph());
            });
    }

    protected function generateSummary(): string
    {
        $summary = "*Past 24 hours*\n\n";

        foreach ($this->getFailedJobCounts() as $job) {
            $percentage = round(
                $this->calculatePercentage(
                    $job['yesterday_count'],
                    $job['ereyesterday_count']
                ),
                2
            );

            if ($percentage > 0) {
                $percentage = '+' . $percentage;
            }

            $summary .= "{$job['name']}\t{$job['yesterday_count']} ({$percentage}%)\n";
        }

        return $summary;
    }

    protected function generateLineGraph(): string
    {
        // TODO Generate and host (possible to upload and host on Slack?) line
        // graph of failed jobs over the last 30 days

        return 'https://dummyimage.com/1165x720/0f172a/f8fafc.png%26text=Line+graph+of+failed+jobs+over+the+last+30+days';
    }

    protected function calculatePercentage(int $current, int $previous): float
    {
        if ($current === 0) {
            return -$previous * 100;
        }

        if ($previous === 0) {
            return $current * 100;
        }

        return (($current - $previous) / $previous) * 100;
    }

    protected function getFailedJobCounts(): array
    {
        // TODO Try getting this data straight from DB using single(?) query

        $now = Carbon::now();

        $query = DB::table(Config::get('queue.failed.table'))
            ->select([
                DB::raw('JSON_EXTRACT(payload, \'$.displayName\') AS name'),
                DB::raw('COUNT(*) AS count'),
            ])
            ->groupBy('name')
            ->orderByDesc('count');

        $ereyesterdayCount = (clone $query)
            ->whereBetween('failed_at', [
                $now->clone()->subDays(2),
                $now
                    ->clone()
                    ->subDay()
                    ->subSecond(),
            ])
            ->get();

        $yesterdayCount = $query
            ->where('failed_at', '>=', $now->clone()->subDay())
            ->get();

        return $yesterdayCount
            ->pluck('name')
            ->merge($ereyesterdayCount->pluck('name'))
            ->unique()
            ->map(function ($name) use ($yesterdayCount, $ereyesterdayCount) {
                $yesterday = $yesterdayCount->firstWhere('name', $name);
                $ereyesterday = $ereyesterdayCount->firstWhere('name', $name);

                return [
                    'name' => str_replace('"', '', stripslashes($name)),
                    'yesterday_count' =>
                        $yesterday !== null ? $yesterday->count : 0,
                    'ereyesterday_count' =>
                        $ereyesterday !== null ? $ereyesterday->count : 0,
                ];
            })
            ->sortByDesc('yesterday_count')
            ->values()
            ->all();
    }
}
