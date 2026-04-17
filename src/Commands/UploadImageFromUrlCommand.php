<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Api\Media\Commands;

use Illuminate\Console\Command;
use Zislogic\Ebay\Api\Media\EbayMediaClient;
use Zislogic\Ebay\Api\Media\Generated\Model\CreateImageFromUrlRequest;
use Zislogic\Ebay\Api\Media\Models\EbayMediaImage;
use Zislogic\Ebay\Connector\Concerns\HandlesEbayApiErrors;

final class UploadImageFromUrlCommand extends Command
{
    use HandlesEbayApiErrors;

    /** @var string */
    protected $signature = 'ebay:media:upload-image-url
        {credential_id : eBay credential ID}
        {url : Source image URL}';

    /** @var string */
    protected $description = 'Upload an image from URL to eBay Picture Services (EPS)';

    public function handle(EbayMediaClient $client): int
    {
        /** @var string $credentialIdStr */
        $credentialIdStr = $this->argument('credential_id');
        $credentialId = (int) $credentialIdStr;
        /** @var string $url */
        $url = $this->argument('url');

        $this->info("Uploading image from URL to EPS...");

        $request = new CreateImageFromUrlRequest();
        $request->setImageUrl($url);

        /** @var \Zislogic\Ebay\Api\Media\Generated\Model\ImageResponse $response */
        $response = $this->callWithRetry(
            fn () => $client->image($credentialId)->createImageFromUrl($request),
        );

        $imageUrl = $response->getImageUrl() ?? '';
        $maxUrl = $response->getMaxDimensionImageUrl();
        $expiration = $response->getExpirationDate();

        EbayMediaImage::query()->create([
            'credential_id' => $credentialId,
            'image_url' => $imageUrl,
            'max_dimension_image_url' => $maxUrl,
            'original_filename' => basename((string) parse_url($url, PHP_URL_PATH)),
            'source_type' => 'url',
            'source_path' => $url,
            'expiration_date' => $expiration,
        ]);

        $this->info("Image uploaded successfully.");
        $this->line("  URL: {$imageUrl}");

        if ($maxUrl !== null) {
            $this->line("  Max dimension URL: {$maxUrl}");
        }

        return self::SUCCESS;
    }
}
