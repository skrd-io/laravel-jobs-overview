<?php

namespace SkrdIo\JobsOverview\Services\ImageCharts;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use SkrdIo\JobsOverview\Models\JobConclusion;

class JobsPerDayChart
{
    private $data;

    public function __construct()
    {
        $this->populateData();
    }

    private function populateData(): void
    {
        $periodDates = $this->getPeriodDates();

        $this->data = array_merge(
            array_combine(
                $periodDates,
                array_map(function ($date) {
                    return [0, 0];
                }, $periodDates)
            ),
            $this->getConclusionsByDate()
                ->keyBy('date')
                ->map(function ($value) {
                    return [(int) $value['successful'], (int) $value['failed']];
                })
                ->toArray()
        );
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function toImageUrl(): string
    {
        return 'https://image-charts.com/chart?' .
            http_build_query([
                'cht' => 'bvs', // Type
                'chs' => '999x618', // Size
                'chd' => $this->getChartData(), // Data
                'chtt' => 'All jobs per day', // Title
                'chl' => $this->getChartLabels(), // Labels
                'chco' => 'f87171,38bdf8', // Colors
                'chdl' => 'Failed|Successful', // Legend
                'chxl' => $this->getChartAxisLabels(), // Axis Labels
                'chxt' => 'x,y', // Visible axes
            ]);
    }

    private function getPeriodDates(): array
    {
        $period = CarbonPeriod::since(Carbon::now()->subDays(7))
            ->days(1)
            ->until(Carbon::now()->subDay());

        return array_map(function ($date) {
            return $date->toDateString();
        }, $period->toArray());
    }

    private function getConclusionsByDate(): Collection
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
                    ->subDays(7)
                    ->toDateString(),
                Carbon::now()->toDateString(),
            ])
            ->groupBy(DB::raw('DATE(concluded_at)'))
            ->get();
    }

    private function getChartData(): string
    {
        $successful = implode(
            ',',
            array_map(function ($value) {
                return $value[0];
            }, $this->data)
        );

        $failed = implode(
            ',',
            array_map(function ($value) {
                return $value[1];
            }, $this->data)
        );

        return 'a:' . $failed . '|' . $successful;
    }

    private function getChartLabels(): string
    {
        $successful = implode(
            '|',
            array_map(function ($value) {
                return round(($value[0] / ($value[0] + $value[1])) * 100) . '%';
            }, $this->data)
        );

        $failed = implode(
            '|',
            array_map(function ($value) {
                return round(($value[1] / ($value[0] + $value[1])) * 100) . '%';
            }, $this->data)
        );

        return $failed . '|' . $successful;
    }

    private function getChartAxisLabels(): string
    {
        return '0:|' .
            implode(
                '|',
                array_map(function ($value) {
                    return Carbon::parse($value)->format('D j M');
                }, array_keys($this->data))
            );
    }
}
