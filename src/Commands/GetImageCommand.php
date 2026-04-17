<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Api\Media\Commands;

use Illuminate\Console\Command;
use Zislogic\Ebay\Api\Media\EbayMediaClient;
use Zislogic\Ebay\Connector\Concerns\HandlesEbayApiErrors;

final class GetImageCommand extends Command
{
    use HandlesEbayApiErrors;

    /** @var string */
    protected $signature = 'ebay:media:get-image
        {credential_id : eBay credential ID}
        {image_id : eBay image ID}';

    /** @var string */
    protected $description = 'Get image details from eBay Picture Services';

    public function handle(EbayMediaClient $client): int
    {
        /** @var string $credentialIdStr */
        $credentialIdStr = $this->argument('credential_id');
        $credentialId = (int) $credentialIdStr;
        /** @var string $imageId */
        $imageId = $this->argument('image_id');

        $this->info("Fetching image details for {$imageId}...");

        /** @var \Zislogic\Ebay\Api\Media\Generated\Model\ImageResponse $response */
        $response = $this->callWithRetry(
            fn () => $client->image($credentialId)->getImage($imageId),
        );

        $this->line("  URL: " . ($response->getImageUrl() ?? 'N/A'));
        $this->line("  Max dimension URL: " . ($response->getMaxDimensionImageUrl() ?? 'N/A'));
        $this->line("  Expires: " . ($response->getExpirationDate() ?? 'N/A'));

        return self::SUCCESS;
    }
}
