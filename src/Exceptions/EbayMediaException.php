<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Api\Media\Exceptions;

use RuntimeException;
use Zislogic\Ebay\Api\Media\Generated\ApiException;

final class EbayMediaException extends RuntimeException
{
    public static function fromApiException(ApiException $e): self
    {
        return new self(
            sprintf('eBay Media API error [%d]: %s', $e->getCode(), $e->getMessage()),
            $e->getCode(),
            $e,
        );
    }

    public static function fileNotFound(string $filePath): self
    {
        return new self(
            sprintf('File not found: %s', $filePath),
        );
    }

    public static function uploadFailed(int $statusCode, string $body): self
    {
        return new self(
            sprintf('Image upload failed [%d]: %s', $statusCode, $body),
        );
    }
}
