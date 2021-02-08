<?php


namespace AlexVanVliet\Migratify\Tests;


use AlexVanVliet\Migratify\Providers\MigratifyServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * Setup the environment.
     *
     * @param Application $app
     */
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
    }

    /**
     * Setup the test.
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Get the service providers from the package.
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            MigratifyServiceProvider::class,
        ];
    }
}
