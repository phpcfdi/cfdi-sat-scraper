<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatException;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\ResourceDownloader;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\SatScraper;

interface SatScraperInterface
{
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
    ): ResourceDownloader;

    /**
     * Initializes session on SAT
     *
     * @return SatScraper
     * @throws LoginException if session is not alive
     */
    public function confirmSessionIsAlive(): SatScraper;

    /**
     * Retrieve the MetadataList using specific UUIDS to download
     *
     * @param string[] $uuids
     * @param DownloadType $downloadType
     * @return MetadataList
     * @throws SatException on session or connection exception
     */
    public function listByUuids(array $uuids, DownloadType $downloadType): MetadataList;

    /**
     * Retrieve the MetadataList based on the query, but uses full days on dates (without time parts)
     *
     * @param QueryByFilters $query
     * @return MetadataList
     * @throws SatException on session or connection exception
     */
    public function listByPeriod(QueryByFilters $query): MetadataList;

    /**
     * Retrieve the MetadataList based on the query, but uses the period considering dates and times
     *
     * @param QueryByFilters $query
     * @return MetadataList
     * @throws SatException on session or connection exception
     */
    public function listByDateTime(QueryByFilters $query): MetadataList;
}
