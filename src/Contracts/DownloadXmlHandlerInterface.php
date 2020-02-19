<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

use PhpCfdi\CfdiSatScraper\Exceptions\DownloadXmlError;
use Psr\Http\Message\ResponseInterface;

interface DownloadXmlHandlerInterface
{
    /**
     * Invoked when the async request promise was fulfilled
     *
     * @param string $uuid
     * @param string $content
     * @param ResponseInterface $response
     */
    public function onSuccess(string $uuid, string $content, ResponseInterface $response): void;

    /**
     * Invoked when the download process had an error.
     *
     * @param DownloadXmlError $error
     */
    public function onError(DownloadXmlError $error): void;
}
