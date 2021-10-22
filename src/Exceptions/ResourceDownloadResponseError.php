<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * This error is thrown when fail to perform an XML download and a ResponseInterface is already available.
 *
 * @method ResponseInterface getReason()
 */
class ResourceDownloadResponseError extends ResourceDownloadError
{
    public function __construct(string $message, string $uuid, ResponseInterface $response, Throwable $previous = null)
    {
        parent::__construct($message, $uuid, $response, $previous);
    }

    public static function invalidStatusCode(ResponseInterface $response, string $uuid): self
    {
        return new self(
            sprintf('Download of CFDI %s return an invalid status code %d', $uuid, $response->getStatusCode()),
            $uuid,
            $response,
        );
    }

    public static function emptyContent(ResponseInterface $response, string $uuid): self
    {
        return new self(sprintf('Download of CFDI %s return an empty body', $uuid), $uuid, $response);
    }

    public static function contentIsNotCfdi(ResponseInterface $response, string $uuid): self
    {
        return new self(sprintf('Download of CFDI %s return something that is not a CFDI', $uuid), $uuid, $response);
    }

    public static function contentIsNotPdf(ResponseInterface $response, string $uuid, string $mimeType): self
    {
        return new self(sprintf('Download of CFDI %s return something that is not a PDF (mime %s)', $uuid, $mimeType), $uuid, $response);
    }

    public static function onSuccessException(ResponseInterface $response, string $uuid, Throwable $throwable): self
    {
        if ($throwable instanceof self) {
            return $throwable;
        }
        return new self(sprintf('Download of CFDI %s was unable to handle fulfill', $uuid), $uuid, $response, $throwable);
    }
}
