<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Promise\PromiseInterface;
use PhpCfdi\CfdiSatScraper\Contracts\ResourceDownloaderPromiseHandlerInterface;
use PhpCfdi\CfdiSatScraper\Contracts\ResourceDownloadHandlerInterface;
use PhpCfdi\CfdiSatScraper\Contracts\ResourceFileNamerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\Exceptions\RuntimeException;
use PhpCfdi\CfdiSatScraper\Internal\ResourceDownloaderPromiseHandler;
use PhpCfdi\CfdiSatScraper\Internal\ResourceDownloadStoreInFolder;
use PhpCfdi\CfdiSatScraper\Internal\ResourceFileNamerByType;
use Traversable;

/**
 * Helper class to make concurrent downloads of CFDI files.
 *
 * It is based on a MetadataList on which each Metadata contains the link to download depending on the resource type.
 * Be aware that it will only download a CFDI if the Metadata link exists.
 *
 * You can use the method `saveTo` that will store all the downloaded files on a specific folder.
 *
 * You can fine tune the download process (success & error) if you implement
 * the `ResourceDownloadHandlerInterface` interface and use the `download` method.
 *
 * The concurrent downloads are based on Guzzle/Promises.
 */
class ResourceDownloader
{
    public const DEFAULT_CONCURRENCY = 10;

    /** @var MetadataList|null */
    protected $list;

    /** @var int */
    protected $concurrency;

    /** @var SatHttpGateway */
    private $satHttpGateway;

    /** @var ResourceType */
    private $resourceType;

    /** @var ResourceFileNamerInterface */
    private $resourceFileNamer;

    public function __construct(
        SatHttpGateway $satHttpGateway,
        ResourceType $resourceType,
        ?MetadataList $list = null,
        int $concurrency = self::DEFAULT_CONCURRENCY,
        ?ResourceFileNamerInterface $resourceFileNamer = null
    ) {
        $this->satHttpGateway = $satHttpGateway;
        $this->resourceType = $resourceType;
        $this->list = $list;
        $this->setConcurrency($concurrency);
        $this->setResourceFileNamer($resourceFileNamer ?? new ResourceFileNamerByType($resourceType));
    }

    public function getResourceType(): ResourceType
    {
        return $this->resourceType;
    }

    public function hasMetadataList(): bool
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

    public function getResourceFileNamer(): ResourceFileNamerInterface
    {
        return $this->resourceFileNamer;
    }

    /**
     * Set up the resource file namer
     *
     * @param ResourceFileNamerInterface $resourceFileNamer
     * @return $this
     */
    public function setResourceFileNamer(ResourceFileNamerInterface $resourceFileNamer): self
    {
        $this->resourceFileNamer = $resourceFileNamer;
        return $this;
    }

    /**
     * Generate the promises to download all the elements on the metadata list that contains
     * a link to download the CFDI.
     *
     * Then the download operation was successful it will call ResourceDownloadHandlerInterface::onSuccess.
     * If some exception was raced when downloading, validating the response or calling onSuccess
     * then it will call ResourceDownloadHandlerInterface::onError.
     *
     * The download will return an array that contains all the successful processed uuids.
     *
     * @param ResourceDownloadHandlerInterface $handler
     * @return string[]
     *
     * @see ResourceDownloaderPromiseHandler::promiseFulfilled()
     * @see ResourceDownloaderPromiseHandler::promiseRejected()
     */
    public function download(ResourceDownloadHandlerInterface $handler): array
    {
        // wrap the provided handler into the main handler, to throw the expected exceptions
        $promisesHandler = $this->makePromiseHandler($handler);
        // create the promises iterator
        $promises = $this->makePromises();
        // create the invoker
        $invoker = new EachPromise($promises, [
            'concurrency' => $this->getConcurrency(),
            'fulfilled' => [$promisesHandler, 'promiseFulfilled'],
            'rejected' => [$promisesHandler, 'promiseRejected'],
        ]);
        // create the promise from the invoker and wait for it to finish
        $invoker->promise()->wait();

        return $promisesHandler->downloadedUuids();
    }

    /**
     * Factory method to make the default ResourceDownloaderPromiseHandler,
     * by extracting the creation it can be replaced with any ResourceDownloaderPromiseHandlerInterface.
     *
     * @param ResourceDownloadHandlerInterface $handler
     * @return ResourceDownloaderPromiseHandlerInterface
     */
    protected function makePromiseHandler(ResourceDownloadHandlerInterface $handler): ResourceDownloaderPromiseHandlerInterface
    {
        return new ResourceDownloaderPromiseHandler($this->getResourceType(), $handler);
    }

    /**
     * Factory method to make a Promise iterator with each item in the Metadata in the MedataList
     * that has a URL to download the XML.
     * By extracting the creation it can be replaced with any iterable.
     *
     * @return Traversable<string, PromiseInterface>|PromiseInterface[]
     */
    protected function makePromises(): Traversable
    {
        foreach ($this->getMetadataList() as $metadata) {
            $link = $metadata->getResource($this->getResourceType());
            if ('' === $link) {
                continue;
            }
            yield $metadata->uuid() => $this->satHttpGateway->getAsync($link);
        }
    }

    /**
     * Generic method to download all the elements on the metadata list that contains a link to download.
     * Before download, it checks that the destination directory exists, if it doesn't exist and call with
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
     *
     * @throws InvalidArgumentException if destination folder argument is empty
     * @throws RuntimeException if didn't ask to create folder and path does not exist
     * @throws RuntimeException if ask to create folder path exists and is not a folder
     * @throws RuntimeException if unable to create folder
     *
     * @see \PhpCfdi\CfdiSatScraper\Internal\ResourceDownloadStoreInFolder
     */
    public function saveTo(string $destinationFolder, bool $createFolder = false, int $createMode = 0775): array
    {
        $storeHandler = new ResourceDownloadStoreInFolder($destinationFolder, $this->getResourceFileNamer());
        $storeHandler->checkDestinationFolder($createFolder, $createMode);
        return $this->download($storeHandler);
    }
}
