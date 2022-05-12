<?php

namespace Tests\Feature\Services\ImageCharts\JobsPerDayChart;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SkrdIo\JobsOverview\Models\JobConclusion;
use SkrdIo\JobsOverview\Services\ImageCharts\JobsPerDayChart;
use Tests\TestCase;

class GetDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2001, 5, 21, 12));
    }

    public function testZeroConclusions(): void
    {
        $this->assertSame(
            [
                '2001-05-14' => [0, 0],
                '2001-05-15' => [0, 0],
                '2001-05-16' => [0, 0],
                '2001-05-17' => [0, 0],
                '2001-05-18' => [0, 0],
                '2001-05-19' => [0, 0],
                '2001-05-20' => [0, 0],
            ],
            (new JobsPerDayChart())->getData()
        );
    }

    public function testConcludedToday(): void
    {
        factory(JobConclusion::class)->create([
            'concluded_at' => Carbon::now(),
        ]);

        $this->assertSame(
            [
                '2001-05-14' => [0, 0],
                '2001-05-15' => [0, 0],
                '2001-05-16' => [0, 0],
                '2001-05-17' => [0, 0],
                '2001-05-18' => [0, 0],
                '2001-05-19' => [0, 0],
                '2001-05-20' => [0, 0],
            ],
            (new JobsPerDayChart())->getData()
        );
    }

    public function testConcludedYesterday(): void
    {
        factory(JobConclusion::class)->create([
            'is_fail' => false,
            'concluded_at' => Carbon::now()->subDay(),
        ]);

        $this->assertSame(
            [
                '2001-05-14' => [0, 0],
                '2001-05-15' => [0, 0],
                '2001-05-16' => [0, 0],
                '2001-05-17' => [0, 0],
                '2001-05-18' => [0, 0],
                '2001-05-19' => [0, 0],
                '2001-05-20' => [1, 0], // 👈
            ],
            (new JobsPerDayChart())->getData()
        );
    }

    public function testConcluded8DaysAgo(): void
    {
        factory(JobConclusion::class)->create([
            'concluded_at' => Carbon::now()->subDays(8),
        ]);

        $this->assertSame(
            [
                '2001-05-14' => [0, 0],
                '2001-05-15' => [0, 0],
                '2001-05-16' => [0, 0],
                '2001-05-17' => [0, 0],
                '2001-05-18' => [0, 0],
                '2001-05-19' => [0, 0],
                '2001-05-20' => [0, 0],
            ],
            (new JobsPerDayChart())->getData()
        );
    }

    public function testConcluded7DaysAgo(): void
    {
        factory(JobConclusion::class)->create([
            'is_fail' => false,
            'concluded_at' => Carbon::now()->subDays(7),
        ]);

        $this->assertSame(
            [
                '2001-05-14' => [1, 0], // 👈
                '2001-05-15' => [0, 0],
                '2001-05-16' => [0, 0],
                '2001-05-17' => [0, 0],
                '2001-05-18' => [0, 0],
                '2001-05-19' => [0, 0],
                '2001-05-20' => [0, 0],
            ],
            (new JobsPerDayChart())->getData()
        );
    }

    public function testSingleSuccessfulConclusion(): void
    {
        factory(JobConclusion::class)->create([
            'is_fail' => false,
            'concluded_at' => Carbon::now()->subDays(4),
        ]);

        $this->assertSame(
            [
                '2001-05-14' => [0, 0],
                '2001-05-15' => [0, 0],
                '2001-05-16' => [0, 0],
                '2001-05-17' => [1, 0], // 👈
                '2001-05-18' => [0, 0],
                '2001-05-19' => [0, 0],
                '2001-05-20' => [0, 0],
            ],
            (new JobsPerDayChart())->getData()
        );
    }

    public function testSingleFailedConclusion(): void
    {
        factory(JobConclusion::class)->create([
            'is_fail' => true,
            'concluded_at' => Carbon::now()->subDays(4),
        ]);

        $this->assertSame(
            [
                '2001-05-14' => [0, 0],
                '2001-05-15' => [0, 0],
                '2001-05-16' => [0, 0],
                '2001-05-17' => [0, 1], // 👈
                '2001-05-18' => [0, 0],
                '2001-05-19' => [0, 0],
                '2001-05-20' => [0, 0],
            ],
            (new JobsPerDayChart())->getData()
        );
    }

    public function testMultipleConclusionsOnSameDay(): void
    {
        factory(JobConclusion::class)->create([
            'is_fail' => false,
            'concluded_at' => Carbon::now()->subDays(4),
        ]);

        factory(JobConclusion::class)->create([
            'is_fail' => true,
            'concluded_at' => Carbon::now()->subDays(4),
        ]);

        $this->assertSame(
            [
                '2001-05-14' => [0, 0],
                '2001-05-15' => [0, 0],
                '2001-05-16' => [0, 0],
                '2001-05-17' => [1, 1], // 👈
                '2001-05-18' => [0, 0],
                '2001-05-19' => [0, 0],
                '2001-05-20' => [0, 0],
            ],
            (new JobsPerDayChart())->getData()
        );
    }

    public function testMultipleSuccessfulConclusionsOnSameDay(): void
    {
        factory(JobConclusion::class)->create([
            'is_fail' => false,
            'concluded_at' => Carbon::now()->subDays(4),
        ]);

        factory(JobConclusion::class)->create([
            'is_fail' => false,
            'concluded_at' => Carbon::now()->subDays(4),
        ]);

        $this->assertSame(
            [
                '2001-05-14' => [0, 0],
                '2001-05-15' => [0, 0],
                '2001-05-16' => [0, 0],
                '2001-05-17' => [2, 0], // 👈
                '2001-05-18' => [0, 0],
                '2001-05-19' => [0, 0],
                '2001-05-20' => [0, 0],
            ],
            (new JobsPerDayChart())->getData()
        );
    }

    public function testMultipleFailedConclusionsOnSameDay(): void
    {
        factory(JobConclusion::class)->create([
            'is_fail' => true,
            'concluded_at' => Carbon::now()->subDays(4),
        ]);

        factory(JobConclusion::class)->create([
            'is_fail' => true,
            'concluded_at' => Carbon::now()->subDays(4),
        ]);

        $this->assertSame(
            [
                '2001-05-14' => [0, 0],
                '2001-05-15' => [0, 0],
                '2001-05-16' => [0, 0],
                '2001-05-17' => [0, 2], // 👈
                '2001-05-18' => [0, 0],
                '2001-05-19' => [0, 0],
                '2001-05-20' => [0, 0],
            ],
            (new JobsPerDayChart())->getData()
        );
    }

    public function testMultipleConclusionsOnDifferentDays(): void
    {
        factory(JobConclusion::class)->create([
            'is_fail' => true,
            'concluded_at' => Carbon::now()->subDays(5),
        ]);

        factory(JobConclusion::class)->create([
            'is_fail' => false,
            'concluded_at' => Carbon::now()->subDays(4),
        ]);

        $this->assertSame(
            [
                '2001-05-14' => [0, 0],
                '2001-05-15' => [0, 0],
                '2001-05-16' => [0, 1], // 👈
                '2001-05-17' => [1, 0], // 👈
                '2001-05-18' => [0, 0],
                '2001-05-19' => [0, 0],
                '2001-05-20' => [0, 0],
            ],
            (new JobsPerDayChart())->getData()
        );
    }

    public function testMultipleSuccessfulConclusionsOnDifferentDays(): void
    {
        factory(JobConclusion::class)->create([
            'is_fail' => false,
            'concluded_at' => Carbon::now()->subDays(5),
        ]);

        factory(JobConclusion::class)->create([
            'is_fail' => false,
            'concluded_at' => Carbon::now()->subDays(4),
        ]);

        $this->assertSame(
            [
                '2001-05-14' => [0, 0],
                '2001-05-15' => [0, 0],
                '2001-05-16' => [1, 0], // 👈
                '2001-05-17' => [1, 0], // 👈
                '2001-05-18' => [0, 0],
                '2001-05-19' => [0, 0],
                '2001-05-20' => [0, 0],
            ],
            (new JobsPerDayChart())->getData()
        );
    }

    public function testMultipleFailedConclusionsOnDifferentDays(): void
    {
        factory(JobConclusion::class)->create([
            'is_fail' => true,
            'concluded_at' => Carbon::now()->subDays(5),
        ]);

        factory(JobConclusion::class)->create([
            'is_fail' => true,
            'concluded_at' => Carbon::now()->subDays(4),
        ]);

        $this->assertSame(
            [
                '2001-05-14' => [0, 0],
                '2001-05-15' => [0, 0],
                '2001-05-16' => [0, 1], // 👈
                '2001-05-17' => [0, 1], // 👈
                '2001-05-18' => [0, 0],
                '2001-05-19' => [0, 0],
                '2001-05-20' => [0, 0],
            ],
            (new JobsPerDayChart())->getData()
        );
    }
}
