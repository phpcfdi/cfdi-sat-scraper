<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

use Psr\Http\Message\ResponseInterface;

interface ResourceDownloaderPromiseHandlerInterface
{
    /**
     * This method handles each promise fulfilled event
     */
    public function promiseFulfilled(ResponseInterface $response, string $uuid): null;

    /**
     * This method handles each promise rejected event
     */
    public function promiseRejected(mixed $reason, string $uuid): null;

    /**
     * Return the list of successfully processed UUIDS
     *
     * @return string[]
     */
    public function downloadedUuids(): array;
}
