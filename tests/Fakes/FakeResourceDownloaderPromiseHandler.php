<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Fakes;

use PhpCfdi\CfdiSatScraper\Contracts\ResourceDownloaderPromiseHandlerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * This implementation of ResourceDownloaderPromiseHandlerInterface set
 * the first input (odds) as successfully downloaded
 * and second input (pairs) as rejected.
 *
 * @internal IMPORTANT: Use this without concurrency, it is not multi thread safe
 *
 */
final class FakeResourceDownloaderPromiseHandler implements ResourceDownloaderPromiseHandlerInterface
{
    /** @var string[] */
    private $downloadedUuids = [];

    /** @var string[] */
    private $rejectedUuids = [];

    /** @var int */
    private $counter = 0;

    /**
     * @param string $uuid
     * @return null
     */
    public function append(string $uuid)
    {
        if (0 === ($this->counter++) % 2) {
            $this->downloadedUuids[] = $uuid;
        } else {
            $this->rejectedUuids[] = $uuid;
        }
        return null;
    }

    public function promiseFulfilled(ResponseInterface $response, string $uuid)
    {
        return $this->append($uuid);
    }

    public function promiseRejected($reason, string $uuid)
    {
        return $this->append($uuid);
    }

    public function downloadedUuids(): array
    {
        return $this->downloadedUuids;
    }

    /** @return string[] */
    public function rejectedUuids(): array
    {
        return $this->rejectedUuids;
    }
}
