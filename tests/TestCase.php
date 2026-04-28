<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Api\Media\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Zislogic\Ebay\Api\Media\EbayMediaServiceProvider;
use Zislogic\Ebay\Connector\EbayConnectorServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            EbayConnectorServiceProvider::class,
            EbayMediaServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('cache.default', 'array');

        $app['config']->set('ebay.environment', 'sandbox');
        $app['config']->set('ebay.credentials.sandbox', [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'redirect_uri' => 'http://localhost/ebay/oauth/callback',
        ]);
        $app['config']->set('ebay.urls.sandbox', [
            'auth' => 'https://auth.sandbox.ebay.com/oauth2/authorize',
            'token' => 'https://api.sandbox.ebay.com/identity/v1/oauth2/token',
            'api' => 'https://api.sandbox.ebay.com',
            'apim' => 'https://apim.sandbox.ebay.com',
            'apiz' => 'https://apiz.sandbox.ebay.com',
        ]);
        $app['config']->set('ebay.scopes.sandbox', [
            'https://api.ebay.com/oauth/api_scope',
        ]);
        $app['config']->set('ebay.verify_ssl', true);
        $app['config']->set('ebay.deletion_notification.verification_token', 'test-token');
        $app['config']->set('ebay.deletion_notification.endpoint_url', 'http://localhost/ebay/account-deletion');
    }
}
