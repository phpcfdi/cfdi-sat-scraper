<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

use PhpCfdi\CfdiSatScraper\Exceptions\XmlDownloadError;
use Psr\Http\Message\ResponseInterface;

interface DownloadXmlHandlerInterface
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
