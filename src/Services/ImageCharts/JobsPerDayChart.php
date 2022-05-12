<?php

namespace SkrdIo\JobsOverview\Services\ImageCharts;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use SkrdIo\JobsOverview\Models\JobConclusion;

class JobsPerDayChart
{
    private function getDates(): array
    {
        $period = CarbonPeriod::since(Carbon::now()->subDays(30))
            ->days(1)
            ->until(Carbon::now()->subDay());

        return array_map(function ($date) {
            return $date->toDateString();
        }, $period->toArray());
    }

    private function getJobConclusions(): Collection
    {
        return JobConclusion::query()
            ->select(
                DB::raw('DATE(concluded_at) AS date'),
                DB::raw(
                    'COUNT(CASE WHEN is_fail = FALSE THEN 1 END) AS successful'
                ),
                DB::raw('COUNT(CASE WHEN is_fail = TRUE THEN 1 END) AS failed')
            )
            ->whereBetween('concluded_at', [
                Carbon::now()
                    ->subDays(30)
                    ->toDateString(),
                Carbon::now()->toDateString(),
            ])
            ->groupBy(DB::raw('DATE(concluded_at)'))
            ->get();
    }

    public function getData(): array
    {
        return array_merge(
            array_combine(
                $this->getDates(),
                array_map(function ($date) {
                    return [0, 0];
                }, $this->getDates())
            ),
            $this->getJobConclusions()
                ->keyBy('date')
                ->map(function ($value) {
                    return [(int) $value['successful'], (int) $value['failed']];
                })
                ->toArray()
        );
    }

    public function toImageUrl(): string
    {
        return 'https://image-charts.com/chart?';
    }
}
