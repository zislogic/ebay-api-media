<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Api\Media\Commands;

use Illuminate\Console\Command;
use Zislogic\Ebay\Api\Media\EbayMediaClient;
use Zislogic\Ebay\Api\Media\Generated\Model\ImageResponse;
use Zislogic\Ebay\Api\Media\Models\EbayMediaImage;
use Zislogic\Ebay\Connector\Concerns\HandlesEbayApiErrors;

final class UploadImageCommand extends Command
{
    use HandlesEbayApiErrors;

    /** @var string */
    protected $signature = 'ebay:media:upload-image
        {credential_id : eBay credential ID}
        {file_path : Path to image file (JPG, PNG, GIF, BMP, TIFF, WEBP, AVIF, HEIC)}';

    /** @var string */
    protected $description = 'Upload a local image file to eBay Picture Services (EPS)';

    public function handle(EbayMediaClient $client): int
    {
        /** @var string $credentialIdStr */
        $credentialIdStr = $this->argument('credential_id');
        $credentialId = (int) $credentialIdStr;
        /** @var string $filePath */
        $filePath = $this->argument('file_path');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return self::FAILURE;
        }

        $this->info("Uploading {$filePath} to EPS...");

        /** @var ImageResponse $response */
        $response = $this->callWithRetry(
            fn () => $client->uploadImageFromFile($credentialId, $filePath),
        );

        $imageUrl = $response->getImageUrl() ?? '';
        $maxUrl = $response->getMaxDimensionImageUrl();
        $expiration = $response->getExpirationDate();

        EbayMediaImage::query()->create([
            'credential_id' => $credentialId,
            'image_url' => $imageUrl,
            'max_dimension_image_url' => $maxUrl,
            'original_filename' => basename($filePath),
            'source_type' => 'file',
            'source_path' => $filePath,
            'expiration_date' => $expiration,
        ]);

        $this->info('Image uploaded successfully.');
        $this->line("  URL: {$imageUrl}");

        if ($maxUrl !== null) {
            $this->line("  Max dimension URL: {$maxUrl}");
        }

        if ($expiration !== null) {
            $this->line("  Expires: {$expiration}");
        }

        return self::SUCCESS;
    }
}
