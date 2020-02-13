<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

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
     * @param callable $callback
     */
    public function download(callable $callback): void
    {
        $promises = $this->makePromises();

        (new EachPromise($promises, [
            'concurrency' => $this->getConcurrency(),
            'fulfilled' => function (ResponseInterface $response) use ($callback): void {
                $callback($response->getBody(), $this->getFileName($response));
            },
        ]))->promise()
            ->wait();
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
            yield $this->satHttpGateway->getAsync($link);
        }
    }

    /**
     * @param ResponseInterface $response
     *
     * @return string
     */
    protected function getFileName(ResponseInterface $response)
    {
        $contentDisposition = $response->getHeaderLine('content-disposition');
        $partsOfContentDisposition = explode(';', $contentDisposition);
        $fileName = str_replace('filename=', '', $partsOfContentDisposition[1] ?? '');

        return strtolower(! empty($fileName) ? $fileName : uniqid() . '.xml');
    }

    /**
     * @param string $path
     * @param bool $createDir
     * @param int $mode
     */
    public function saveTo(string $path, bool $createDir = false, int $mode = 0775): void
    {
        if (! $createDir && ! file_exists($path)) {
            throw new \InvalidArgumentException("The provider path [{$path}] not exists");
        }

        if ($createDir && ! file_exists($path)) {
            mkdir($path, $mode, true);
        }

        $this->download(
            function ($content, $name) use ($path): void {
                file_put_contents($path . DIRECTORY_SEPARATOR . $name, $content);
            }
        );
    }
}
