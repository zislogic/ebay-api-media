<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Api\Media;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Zislogic\Ebay\Api\Media\Exceptions\EbayMediaException;
use Zislogic\Ebay\Api\Media\Generated\Api\DocumentApi;
use Zislogic\Ebay\Api\Media\Generated\Api\ImageApi;
use Zislogic\Ebay\Api\Media\Generated\Api\VideoApi;
use Zislogic\Ebay\Api\Media\Generated\Configuration;
use Zislogic\Ebay\Api\Media\Generated\Model\ImageResponse;
use Zislogic\Ebay\Connector\Services\EbayHttpClient;

final class EbayMediaClient
{
    private readonly Configuration $configuration;
    private readonly Client $guzzleClient;

    public function __construct(
        private readonly EbayHttpClient $httpClient,
        string $apimBaseUrl,
        bool $verifySsl = true,
        ?string $proxy = null,
    ) {
        $this->configuration = new Configuration();
        $this->configuration->setHost($apimBaseUrl . '/commerce/media/v1_beta');

        $guzzleOptions = ['verify' => $verifySsl];
        if ($proxy !== null) {
            $guzzleOptions['proxy'] = $proxy;
        }

        $this->guzzleClient = new Client($guzzleOptions);
    }

    public function image(int $credentialId): ImageApi
    {
        $this->refreshSellerToken($credentialId);

        return new ImageApi(
            $this->guzzleClient,
            $this->configuration,
        );
    }

    public function video(int $credentialId): VideoApi
    {
        $this->refreshSellerToken($credentialId);

        return new VideoApi(
            $this->guzzleClient,
            $this->configuration,
        );
    }

    public function document(int $credentialId): DocumentApi
    {
        $this->refreshSellerToken($credentialId);

        return new DocumentApi(
            $this->guzzleClient,
            $this->configuration,
        );
    }

    /**
     * Upload an image file to eBay Picture Services (EPS) via multipart POST.
     *
     * The generated ImageApi::createImageFromFile() is broken (no file parameter),
     * so this method uses Guzzle multipart directly.
     */
    public function uploadImageFromFile(int $credentialId, string $filePath): ImageResponse
    {
        if (!file_exists($filePath)) {
            throw EbayMediaException::fileNotFound($filePath);
        }

        $token = $this->httpClient->getSellerAccessToken($credentialId);
        $url = $this->configuration->getHost() . '/image/create_image_from_file';

        $response = $this->guzzleClient->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
            'multipart' => [
                [
                    'name' => 'image',
                    'contents' => Utils::tryFopen($filePath, 'r'),
                    'filename' => basename($filePath),
                ],
            ],
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode >= 300) {
            throw EbayMediaException::uploadFailed(
                $statusCode,
                $response->getBody()->getContents(),
            );
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        $imageResponse = new ImageResponse();

        /** @var string|null $imageUrl */
        $imageUrl = $data['imageUrl'] ?? null;
        if ($imageUrl !== null) {
            $imageResponse->setImageUrl($imageUrl);
        }

        /** @var string|null $maxUrl */
        $maxUrl = $data['maxDimensionImageUrl'] ?? null;
        if ($maxUrl !== null) {
            $imageResponse->setMaxDimensionImageUrl($maxUrl);
        }

        /** @var string|null $expDate */
        $expDate = $data['expirationDate'] ?? null;
        if ($expDate !== null) {
            $imageResponse->setExpirationDate($expDate);
        }

        return $imageResponse;
    }

    private function refreshSellerToken(int $credentialId): void
    {
        $this->configuration->setAccessToken(
            $this->httpClient->getSellerAccessToken($credentialId)
        );
    }
}
