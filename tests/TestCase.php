<?php

namespace romanzipp\MailCheck\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use Orchestra\Testbench\TestCase as BaseTestCase;
use romanzipp\MailCheck\Api\MailCheckApi;
use romanzipp\MailCheck\Checker;

abstract class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate')->run();
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('mailcheck.store_checks', false);
        $app['config']->set('mailcheck.cache_checks', false);
    }

    /**
     * Get package providers.  At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \romanzipp\MailCheck\Providers\MailCheckProvider::class,
        ];
    }

    protected function mockChecker($responses): Checker
    {
        $checker = new Checker();
        $checker->api = new MailCheckApi();
        $checker->api->client = new Client([
            'handler' => new MockHandler($responses),
        ]);

        return $checker;
    }
}
