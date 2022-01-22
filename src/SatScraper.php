<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Contracts\MaximumRecordsHandler;
use PhpCfdi\CfdiSatScraper\Contracts\SatScraperInterface;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Internal\MetadataDownloader;
use PhpCfdi\CfdiSatScraper\Internal\NullMaximumRecordsHandler;
use PhpCfdi\CfdiSatScraper\Internal\QueryResolver;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;

class SatScraper implements SatScraperInterface
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

    public function resourceDownloader(
        ResourceType $resourceType = null,
        ?MetadataList $metadataList = null,
        int $concurrency = ResourceDownloader::DEFAULT_CONCURRENCY
    ): ResourceDownloader {
        $resourceType = $resourceType ?? ResourceType::xml();
        return new ResourceDownloader($this->satHttpGateway, $resourceType, $metadataList, $concurrency);
    }

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

    public function listByUuids(array $uuids, DownloadType $downloadType): MetadataList
    {
        $this->confirmSessionIsAlive();
        return $this->createMetadataDownloader()->downloadByUuids($uuids, $downloadType);
    }

    public function listByPeriod(QueryByFilters $query): MetadataList
    {
        $this->confirmSessionIsAlive();
        return $this->createMetadataDownloader()->downloadByDate($query);
    }

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
