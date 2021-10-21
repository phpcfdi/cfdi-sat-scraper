<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Contracts\MaximumRecordsHandler;
use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Internal\MetadataDownloader;
use PhpCfdi\CfdiSatScraper\Internal\NullMaximumRecordsHandler;
use PhpCfdi\CfdiSatScraper\Internal\QueryResolver;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;

class SatScraper
{
    /** @var SessionManager */
    private $sessionManager;

    /** @var SatHttpGateway */
    private $satHttpGateway;

    /** @var MaximumRecordsHandler */
    protected $maximumRecordsHandler;

    /**
     * SatScraper constructor.
     *
     * @param SessionManager $sessionManager
     * @param SatHttpGateway|null $satHttpGateway
     * @param MaximumRecordsHandler|null $maximumRecordsHandler
     */
    public function __construct(
        SessionManager $sessionManager,
        ?SatHttpGateway $satHttpGateway = null,
        ?MaximumRecordsHandler $maximumRecordsHandler = null
    ) {
        $this->sessionManager = $sessionManager;
        $this->satHttpGateway = $satHttpGateway ?? $this->createDefaultSatHttpGateway();
        $this->maximumRecordsHandler = $maximumRecordsHandler ?? new NullMaximumRecordsHandler();
    }

    /**
     * Method factory to create a MetadataDownloader
     *
     * @internal
     */
    protected function createMetadataDownloader(): MetadataDownloader
    {
        return new MetadataDownloader($this->createQueryResolver(), $this->maximumRecordsHandler);
    }

    /**
     * Method factory to create a SatHttpGateway
     *
     * @internal
     */
    protected function createDefaultSatHttpGateway(): SatHttpGateway
    {
        return new SatHttpGateway();
    }

    /**
     * Method factory to create a QueryResolver
     *
     * @internal
     */
    protected function createQueryResolver(): QueryResolver
    {
        return new QueryResolver($this->satHttpGateway);
    }

    /**
     * Create a ResourceDownloader object with (optionally) a MetadataList.
     * Use the ResourceDownloader to retrieve the CFDI related files.
     *
     * @param ResourceType|null $resourceType
     * @param MetadataList|null $metadataList
     * @param int $concurrency
     * @return ResourceDownloader
     */
    public function resourceDownloader(
        ResourceType $resourceType = null,
        ?MetadataList $metadataList = null,
        int $concurrency = ResourceDownloader::DEFAULT_CONCURRENCY
    ): ResourceDownloader {
        $resourceType = $resourceType ?? ResourceType::xml();
        return new ResourceDownloader($this->satHttpGateway, $resourceType, $metadataList, $concurrency);
    }

    /**
     * Initializes session on SAT
     *
     * @return SatScraper
     * @throws LoginException if session is not alive
     */
    public function confirmSessionIsAlive(): self
    {
        $sessionManager = $this->getSessionManager();
        $sessionManager->setHttpGateway($this->getSatHttpGateway());

        if (! $sessionManager->hasLogin()) {
            $sessionManager->login();
        }
        $sessionManager->accessPortalMainPage();

        return $this;
    }

    /**
     * Retrieve the MetadataList using specific UUIDS to download
     *
     * @param string[] $uuids
     * @param DownloadType $downloadType
     * @return MetadataList
     * @throws LoginException
     * @throws SatHttpGatewayException
     */
    public function listByUuids(array $uuids, DownloadType $downloadType): MetadataList
    {
        $this->confirmSessionIsAlive();
        return $this->createMetadataDownloader()->downloadByUuids($uuids, $downloadType);
    }

    /**
     * Retrieve the MetadataList based on the query, but uses full days on dates (without time parts)
     *
     * @param QueryByFilters $query
     * @return MetadataList
     * @throws LoginException
     * @throws SatHttpGatewayException
     */
    public function listByPeriod(QueryByFilters $query): MetadataList
    {
        $this->confirmSessionIsAlive();
        return $this->createMetadataDownloader()->downloadByDate($query);
    }

    /**
     * Retrieve the MetadataList based on the query, but uses the period considering dates and times
     *
     * @param QueryByFilters $query
     * @return MetadataList
     * @throws LoginException
     * @throws SatHttpGatewayException
     */
    public function listByDateTime(QueryByFilters $query): MetadataList
    {
        $this->confirmSessionIsAlive();
        return $this->createMetadataDownloader()->downloadByDateTime($query);
    }

    public function getSessionManager(): SessionManager
    {
        return $this->sessionManager;
    }

    public function getSatHttpGateway(): SatHttpGateway
    {
        return $this->satHttpGateway;
    }

    public function getMaximumRecordsHandler(): MaximumRecordsHandler
    {
        return $this->maximumRecordsHandler;
    }
}
