<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use SkrdIo\JobsOverview\JobsOverviewServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [JobsOverviewServiceProvider::class];
    }
}
