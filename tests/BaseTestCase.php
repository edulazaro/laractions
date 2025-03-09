<?php

namespace EduLazaro\Laractions\Tests;

use Orchestra\Testbench\TestCase as Testbench;

abstract class BaseTestCase extends Testbench
{
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Load package-specific service providers.
     */
    protected function getPackageProviders($app)
    {
        return [

        ];
    }
}
