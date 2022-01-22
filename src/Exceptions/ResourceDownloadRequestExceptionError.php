<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use GuzzleHttp\Exception\RequestException;

/**
 * This error is thrown when fail to perform an XML download and a RequestException is available.
 *
 * @method RequestException getReason()
 */
class ResourceDownloadRequestExceptionError extends ResourceDownloadError
{
    public function __construct(string $message, string $uuid, RequestException $reason)
    {
        parent::__construct($message, $uuid, $reason);
    }

    public static function onRequestException(RequestException $exception, string $uuid): self
    {
        return new self(sprintf('Download of CFDI %s fails when performing request', $uuid), $uuid, $exception);
    }
}
