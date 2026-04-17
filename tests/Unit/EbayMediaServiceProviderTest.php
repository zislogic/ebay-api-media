<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Api\Media\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Zislogic\Ebay\Api\Media\EbayMediaClient;
use Zislogic\Ebay\Api\Media\Tests\TestCase;

final class EbayMediaServiceProviderTest extends TestCase
{
    #[Test]
    public function it_resolves_media_client_from_container(): void
    {
        $client = $this->app->make(EbayMediaClient::class);

        $this->assertInstanceOf(EbayMediaClient::class, $client);
    }

    #[Test]
    public function it_registers_media_client_as_singleton(): void
    {
        $first = $this->app->make(EbayMediaClient::class);
        $second = $this->app->make(EbayMediaClient::class);

        $this->assertSame($first, $second);
    }

    #[Test]
    public function it_merges_media_config(): void
    {
        $this->assertIsArray(config('ebay-api-media'));
    }
}
