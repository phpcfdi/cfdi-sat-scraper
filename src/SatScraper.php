<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Internal\MetadataDownloader;
use PhpCfdi\CfdiSatScraper\Internal\QueryResolver;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;

class SatScraper
{
    /** @var callable|null */
    protected $onFiveHundred;

    /** @var SatHttpGateway */
    private $satHttpGateway;

    /** @var SessionManager */
    private $sessionManager;

    /**
     * SatScraper constructor.
     *
     * @param SessionManager $sessionManager
     * @param SatHttpGateway|null $satHttpGateway
     * @param callable|null $onFiveHundred
     */
    public function __construct(
        SessionManager $sessionManager,
        ?SatHttpGateway $satHttpGateway = null,
        ?callable $onFiveHundred = null
    ) {
        $this->sessionManager = $sessionManager;
        $this->satHttpGateway = $satHttpGateway ?? $this->createDefaultSatHttpGateway();
        $this->onFiveHundred = $onFiveHundred;
    }

    /**
     * Create a new configured instance of MetadataDownloader.
     * Is a protected method because is not intended to be used from the outside.
     *
     * @return MetadataDownloader
     * @internal
     */
    public function metadataDownloader(): MetadataDownloader
    {
        return new MetadataDownloader($this->createQueryResolver(), $this->onFiveHundred);
    }

    /**
     * Create a ResourceDownloader object with (optionally) a MetadataList.
     * The ResourceDownloader object can be used to retrieve the CFDI XML contents.
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
     * Method factory to create a SatHttpGateway
     *
     * @internal
     * @return SatHttpGateway
     */
    protected function createDefaultSatHttpGateway(): SatHttpGateway
    {
        return new SatHttpGateway();
    }

    /**
     * Method factory to create a QueryResolver
     *
     * @internal
     * @return QueryResolver
     */
    protected function createQueryResolver(): QueryResolver
    {
        return new QueryResolver($this->satHttpGateway);
    }

    /**
     * Initializates session on SAT
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
        return $this->metadataDownloader()->downloadByUuids($uuids, $downloadType);
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
        return $this->metadataDownloader()->downloadByDate($query);
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
        return $this->metadataDownloader()->downloadByDateTime($query);
    }

    public function getSessionManager(): SessionManager
    {
        return $this->sessionManager;
    }

    public function getSatHttpGateway(): SatHttpGateway
    {
        return $this->satHttpGateway;
    }

    public function getOnFiveHundred(): ?callable
    {
        return $this->onFiveHundred;
    }
}
