<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class DownloadXML.
 */
class DownloadXML
{
    /** @var MetadataList|null */
    protected $list;

    /** @var int */
    protected $concurrency;

    /** @var SatHttpGateway */
    private $satHttpGateway;

    public function __construct(SatHttpGateway $satHttpGateway)
    {
        $this->satHttpGateway = $satHttpGateway;
        $this->setConcurrency(10);
        $this->list = null;
    }

    public function hasMetatadaList(): bool
    {
        return $this->list instanceof MetadataList;
    }

    public function getMetadataList(): MetadataList
    {
        if (null === $this->list) {
            throw new \LogicException('The metadata list has not been set');
        }
        return $this->list;
    }

    public function setMetadataList(MetadataList $list): self
    {
        $this->list = $list;
        return $this;
    }

    public function getConcurrency(): int
    {
        return $this->concurrency;
    }

    /**
     * Set concurrency, if lower than 1 will use 1
     *
     * @param int $concurrency
     * @return $this
     */
    public function setConcurrency(int $concurrency): self
    {
        $this->concurrency = max(1, $concurrency);
        return $this;
    }

    /**
     * Generate the promises to download all the elements on the metadata list that contains
     * a link to download. When the promise is fulfilled will call $onFulfilled, if it is rejected
     * will call $onRejected.
     *
     * - $onFulfilled callable: function(string $content, string $filename, string $uuid): void
     * - $onRejected function(ResponseInterface $response, string $uuid): void
     *
     * @param callable $onFulfilled
     * @param callable|null $onRejected
     */
    public function download(callable $onFulfilled, ?callable $onRejected = null): void
    {
        $promises = $this->makePromises();
        $invoker = new EachPromise($promises, [
            'concurrency' => $this->getConcurrency(),
            'fulfilled' => function (ResponseInterface $response, string $uuid) use ($onFulfilled): void {
                $onFulfilled((string) $response->getBody(), $this->getFileName($uuid), $uuid);
            },
            'rejected' => $onRejected,
        ]);
        $invoker->promise()->wait();
    }

    /**
     * @return \Generator|PromiseInterface[]
     */
    protected function makePromises()
    {
        foreach ($this->getMetadataList() as $metadata) {
            $link = $metadata->get('urlXml');
            if ('' === $link) {
                continue;
            }
            yield $metadata->uuid() => $this->satHttpGateway->getAsync($link);
        }
    }

    /**
     * @param string $uuid
     *
     * @return string
     */
    protected function getFileName(string $uuid): string
    {
        return strtolower($uuid) . '.xml';
    }

    /**
     * Generic method to download all the elements on the metadata list that contains a link to download.
     * Before download it checks that the destination directory exists, if it doesn't exists and call with
     * true in $createDir then the directory will be created recursively using mode $mode.
     *
     * When one of the downloads fails it will throw an exception.
     *
     * @param string $destinationDir
     * @param bool $createDir
     * @param int $mode
     */
    public function saveTo(string $destinationDir, bool $createDir = false, int $mode = 0775): void
    {
        if (! $createDir && ! file_exists($destinationDir)) {
            throw new \InvalidArgumentException("The provider path [{$destinationDir}] not exists");
        }

        if ($createDir && ! file_exists($destinationDir)) {
            mkdir($destinationDir, $mode, true);
        }

        $this->download(
            function (string $content, string $name, string $uuid) use ($destinationDir): void {
                if ('' === $content) {
                    throw new \RuntimeException(sprintf('Downloaded CFDI %s was empty', $uuid));
                }
                $destinationFile = $destinationDir . DIRECTORY_SEPARATOR . $name;
                if (false === file_put_contents($destinationFile, $content)) {
                    throw new \RuntimeException(sprintf('Unable to save CFDI %s to %s', $uuid, $destinationFile));
                }
            },
            function (RequestException $exception, string $uuid): void {
                $uri = (string) $exception->getRequest()->getUri();
                throw new \RuntimeException(sprintf('Unable to retrieve CFDI %s from %s', $uuid, $uri), 0, $exception);
            }
        );
    }
}
