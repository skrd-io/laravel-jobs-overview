<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use SkrdIo\JobsOverview\JobsOverviewServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/../database/factories');
    }

    protected function getPackageProviders($app): array
    {
        return [JobsOverviewServiceProvider::class];
    }
}
