<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Api\Media;

use Illuminate\Support\ServiceProvider;
use Zislogic\Ebay\Api\Media\Commands\GetImageCommand;
use Zislogic\Ebay\Api\Media\Commands\UploadImageCommand;
use Zislogic\Ebay\Api\Media\Commands\UploadImageFromUrlCommand;
use Zislogic\Ebay\Connector\Services\EbayHttpClient;

final class EbayMediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ebay-api-media.php', 'ebay-api-media');

        $this->app->singleton(EbayMediaClient::class, function ($app): EbayMediaClient {
            /** @var EbayHttpClient $httpClient */
            $httpClient = $app->make(EbayHttpClient::class);

            /** @var array<string, mixed> $config */
            $config = $app['config']['ebay'];
            $environment = (string) ($config['environment'] ?? 'sandbox');
            $apimBaseUrl = (string) $config['urls'][$environment]['apim'];
            $verifySsl = (bool) ($config['verify_ssl'] ?? true);
            $proxy = EbayHttpClient::resolveProxy($config);

            return new EbayMediaClient($httpClient, $apimBaseUrl, $verifySsl, $proxy);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/ebay-api-media.php' => config_path('ebay-api-media.php'),
        ], 'ebay-api-media-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'ebay-api-media-migrations');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                UploadImageCommand::class,
                UploadImageFromUrlCommand::class,
                GetImageCommand::class,
            ]);
        }
    }
}
