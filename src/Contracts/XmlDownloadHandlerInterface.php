<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

use PhpCfdi\CfdiSatScraper\Exceptions\XmlDownloadError;
use Psr\Http\Message\ResponseInterface;

/**
 * This kind of objects handles the success and error events when downloading XML CFDI files.
 */
interface XmlDownloadHandlerInterface
{
    /**
     * Invoked when the CFDI XML was successfully downloaded
     *
     * @param string $uuid
     * @param string $content
     * @param ResponseInterface $response
     */
    public function onSuccess(string $uuid, string $content, ResponseInterface $response): void;

    /**
     * Invoked when the CFDI XML was unsuccessfully downloaded creating an error
     * Remember that XmlDownloadError can be a more specific class.
     *
     * @param XmlDownloadError $error
     */
    public function onError(XmlDownloadError $error): void;
}
