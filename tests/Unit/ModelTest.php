<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Api\Media\Tests\Unit;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Zislogic\Ebay\Api\Media\Models\EbayMediaImage;
use Zislogic\Ebay\Api\Media\Tests\TestCase;

final class ModelTest extends TestCase
{
    #[Test]
    public function it_creates_media_image(): void
    {
        $image = EbayMediaImage::query()->create([
            'credential_id' => 1,
            'image_url' => 'https://i.ebayimg.com/images/test.jpg',
            'max_dimension_image_url' => 'https://i.ebayimg.com/images/test_max.jpg',
            'original_filename' => 'test.jpg',
            'source_type' => 'file',
            'source_path' => '/tmp/test.jpg',
            'expiration_date' => now()->addDays(30),
        ]);

        $this->assertDatabaseHas('ebay_media_images', [
            'credential_id' => 1,
            'original_filename' => 'test.jpg',
            'source_type' => 'file',
        ]);

        $this->assertEquals('https://i.ebayimg.com/images/test.jpg', $image->image_url);
        $this->assertInstanceOf(Carbon::class, $image->expiration_date);
    }

    #[Test]
    public function it_scopes_by_credential(): void
    {
        EbayMediaImage::query()->create([
            'credential_id' => 1,
            'image_url' => 'https://i.ebayimg.com/1.jpg',
            'source_type' => 'file',
            'source_path' => '/tmp/1.jpg',
        ]);

        EbayMediaImage::query()->create([
            'credential_id' => 2,
            'image_url' => 'https://i.ebayimg.com/2.jpg',
            'source_type' => 'url',
            'source_path' => 'https://example.com/2.jpg',
        ]);

        $this->assertCount(1, EbayMediaImage::forCredential(1)->get());
        $this->assertCount(1, EbayMediaImage::forCredential(2)->get());
    }
}
