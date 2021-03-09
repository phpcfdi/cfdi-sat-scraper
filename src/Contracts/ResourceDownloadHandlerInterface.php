<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadError;
use Psr\Http\Message\ResponseInterface;

/**
 * This kind of objects handles the success and error events when downloading resource files.
 */
interface ResourceDownloadHandlerInterface
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
     * Remember that ResourceDownloadError can be a more specific class.
     *
     * @param ResourceDownloadError $error
     */
    public function onError(ResourceDownloadError $error): void;
}
