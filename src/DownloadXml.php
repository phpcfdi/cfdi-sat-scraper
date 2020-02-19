<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Promise\PromiseInterface;
use PhpCfdi\CfdiSatScraper\Contracts\DownloadXmlHandlerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\Internal\DownloadXmlMainHandler;
use PhpCfdi\CfdiSatScraper\Internal\DownloadXmlStoreInFolder;

/**
 * Helper class to make concurrent downloads of CFDI files.
 *
 * It is based on a MetadataList on which each Metadata contains the link to download.
 * Be aware that it will only download a CFDI if the Metadata `urlXml` value exists.
 *
 * You can use the method `saveTo` that will store all the downloaded files as destination/uuid.xml
 *
 * You can fine tune the download process (success & error) if you implement
 * the `DownloadXmlHandlerInterface` interface and use the `download` method.
 *
 * The concurrent downloads are based on Guzzle/Promises.
 */
class DownloadXml
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
            throw LogicException::generic('The metadata list has not been set');
        }
        return $this->list;
    }

    /**
     * Change the metadata list that will be used to perform downloads
     *
     * @param MetadataList $list
     * @return $this
     */
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
     * a link to download the CFDI.
     *
     * Then the download operation was successful it will call DownloadXmlHandlerInterface::onSuccess.
     * If some exception was raced when downloading, validating the response or calling onSuccess
     * then it will call DownloadXmlHandlerInterface::onError.
     *
     * The download will return an array that contains all the successful processed uuids.
     *
     * @param DownloadXmlHandlerInterface $handler
     * @return string[]
     *
     * @see DownloadXmlMainHandler::promiseFulfilled()
     * @see DownloadXmlMainHandler::promiseRejected()
     */
    public function download(DownloadXmlHandlerInterface $handler): array
    {
        // wrap the privided handler into the main handler, to throw the expected exceptions
        $mainHandler = new DownloadXmlMainHandler($handler);
        // create the promises iterator
        $promises = $this->makePromises();
        // create the invoker
        $invoker = new EachPromise($promises, [
            'concurrency' => $this->getConcurrency(),
            'fulfilled' => [$mainHandler, 'promiseFulfilled'],
            'rejected' => [$mainHandler, 'promiseRejected'],
        ]);
        // create the promise from the invoker and wait for it to finish
        $invoker->promise()->wait();
        return $mainHandler->downloadedUuids();
    }

    /**
     * Create the Promise for each item in the Metadata in the MedataList that contains an `urlXml`
     *
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
     * Generic method to download all the elements on the metadata list that contains a link to download.
     * Before download it checks that the destination directory exists, if it doesn't exists and call with
     * true in $createDir then the directory will be created recursively using mode $mode.
     *
     * When one of the downloads fails it will throw an exception.
     *
     * Return the list of fulfilled UUID
     *
     * @param string $destinationFolder
     * @param bool $createFolder
     * @param int $createMode
     * @return string[]
     */
    public function saveTo(string $destinationFolder, bool $createFolder = false, int $createMode = 0775): array
    {
        $storeHandler = new DownloadXmlStoreInFolder($destinationFolder);
        $storeHandler->checkDestinationFolder($createFolder, $createMode);
        return $this->download($storeHandler);
    }
}
