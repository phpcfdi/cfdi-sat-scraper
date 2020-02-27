<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

use Psr\Http\Message\ResponseInterface;

interface XmlDownloaderPromiseHandlerInterface
{
    /**
     * This method handles the each promise fulfilled event
     *
     * @param ResponseInterface $response
     * @param string $uuid
     * @return null
     */
    public function promiseFulfilled(ResponseInterface $response, string $uuid);

    /**
     * This method handles the each promise rejected event
     *
     * @param mixed $reason
     * @param string $uuid
     * @return null
     */
    public function promiseRejected($reason, string $uuid);

    /**
     * Return the list of succesfully processed UUIDS
     *
     * @return string[]
     */
    public function downloadedUuids(): array;
}
