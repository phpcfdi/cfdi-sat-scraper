<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

use Psr\Http\Message\ResponseInterface;

interface ResourceDownloaderPromiseHandlerInterface
{
    /**
     * This method handles each promise fulfilled event
     * @return null
     */
    public function promiseFulfilled(ResponseInterface $response, string $uuid);

    /**
     * This method handles each promise rejected event
     *
     * @return null
     */
    public function promiseRejected(mixed $reason, string $uuid);

    /**
     * Return the list of successfully processed UUIDS
     *
     * @return string[]
     */
    public function downloadedUuids(): array;
}
