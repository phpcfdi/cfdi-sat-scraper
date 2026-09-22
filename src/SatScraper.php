<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Contracts\MetadataMessageHandler;
use PhpCfdi\CfdiSatScraper\Contracts\SatScraperInterface;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Internal\CommonMethodsScraperTrait;
use PhpCfdi\CfdiSatScraper\Internal\MetadataDownloader;
use PhpCfdi\CfdiSatScraper\Internal\QueryResolver;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;

class SatScraper implements SatScraperInterface
{
    use CommonMethodsScraperTrait;

    protected MetadataMessageHandler $metadataMessageHandler;

    public function __construct(
        SessionManager $sessionManager,
        ?SatHttpGateway $satHttpGateway = null,
        ?MetadataMessageHandler $metadataMessageHandler = null,
    ) {
        $this->sessionManager = $sessionManager;
        $this->satHttpGateway = $satHttpGateway ?? $this->createDefaultSatHttpGateway();
        $this->metadataMessageHandler = $metadataMessageHandler ?? new NullMetadataMessageHandler();
    }

    /**
     * Method factory to create a MetadataDownloader
     *
     * @internal
     */
    protected function createMetadataDownloader(): MetadataDownloader
    {
        return new MetadataDownloader($this->createQueryResolver(), $this->metadataMessageHandler);
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
        ?ResourceType $resourceType = null,
        ?MetadataList $metadataList = null,
        int $concurrency = ResourceDownloader::DEFAULT_CONCURRENCY,
    ): ResourceDownloader {
        $resourceType ??= ResourceType::xml();
        return new ResourceDownloader($this->satHttpGateway, $resourceType, $metadataList, $concurrency);
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

    public function getMetadataMessageHandler(): MetadataMessageHandler
    {
        return $this->metadataMessageHandler;
    }
}
