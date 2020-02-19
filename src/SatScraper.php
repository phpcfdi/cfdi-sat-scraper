<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Internal\MetadataDownloader;
use PhpCfdi\CfdiSatScraper\Internal\QueryResolver;
use PhpCfdi\CfdiSatScraper\Internal\SatSessionManager;

class SatScraper
{
    /** @var SatSessionData */
    private $satSessionData;

    /** @var callable|null */
    protected $onFiveHundred;

    /** @var SatHttpGateway */
    private $satHttpGateway;

    /**
     * SatScraper constructor.
     *
     * @param SatSessionData $sessionData
     * @param SatHttpGateway|null $satHttpGateway
     * @param callable|null $onFiveHundred
     */
    public function __construct(
        SatSessionData $sessionData,
        ?SatHttpGateway $satHttpGateway = null,
        ?callable $onFiveHundred = null
    ) {
        $this->satHttpGateway = $satHttpGateway ?? $this->createDefaultSatHttpGateway();
        $this->satSessionData = $sessionData;
        $this->onFiveHundred = $onFiveHundred;
    }

    protected function createDefaultSatHttpGateway(): SatHttpGateway
    {
        return new SatHttpGateway();
    }

    protected function createSessionManager(): SatSessionManager
    {
        return new SatSessionManager($this->satSessionData, $this->getSatHttpGateway());
    }

    public function createMetadataDownloader(): MetadataDownloader
    {
        return new MetadataDownloader(new QueryResolver($this->satHttpGateway), $this->onFiveHundred);
    }

    /**
     * Create a DownloadXml object with (optionally) a MetadataList.
     * The DownloadXml object can be used to retrieve the CFDI XML contents.
     *
     * @param MetadataList|null $metadataList
     * @param int $concurrency
     * @return DownloadXml
     */
    public function createXmlDownloader(?MetadataList $metadataList = null, int $concurrency = 10): DownloadXml
    {
        return new DownloadXml($this->satHttpGateway, $metadataList, $concurrency);
    }

    public function getSatSessionData(): SatSessionData
    {
        return $this->satSessionData;
    }

    public function getSatHttpGateway(): SatHttpGateway
    {
        return $this->satHttpGateway;
    }

    public function getOnFiveHundred(): ?callable
    {
        return $this->onFiveHundred;
    }

    /**
     * Initializates session on SAT
     *
     * @return SatScraper
     * @throws LoginException if session is not alive
     */
    public function confirmSessionIsAlive(): self
    {
        $this->createSessionManager()->initSession();

        return $this;
    }

    /**
     * Retrieve the MetadataList using specific UUIDS to download
     *
     * @param array $uuids
     * @param DownloadTypesOption $downloadType
     * @return MetadataList
     * @throws LoginException
     */
    public function downloadListUUID(array $uuids, DownloadTypesOption $downloadType): MetadataList
    {
        $this->confirmSessionIsAlive();
        return $this->createMetadataDownloader()->downloadByUuids($uuids, $downloadType);
    }

    /**
     * Retrieve the MetadataList based on the query, but uses full days on dates (without time parts)
     *
     * @param Query $query
     * @return MetadataList
     * @throws LoginException
     */
    public function downloadPeriod(Query $query): MetadataList
    {
        $this->confirmSessionIsAlive();
        return $this->createMetadataDownloader()->downloadByDate($query);
    }

    /**
     * Retrieve the MetadataList based on the query, but uses the period considering dates and times
     *
     * @param Query $query
     * @return MetadataList
     * @throws LoginException
     */
    public function downloadByDateTime(Query $query): MetadataList
    {
        $this->confirmSessionIsAlive();
        return $this->createMetadataDownloader()->downloadByDateTime($query);
    }

    /**
     * Create a DownloadXml object with (optionally) a MetadataList.
     * The DownloadXml object can be used to retrieve the CFDI XML contents.
     *
     * @param MetadataList|null $metadataList
     * @return DownloadXml
     * @deprecated
     */
    public function downloader(?MetadataList $metadataList = null): DownloadXml
    {
        return $this->createXmlDownloader($metadataList);
    }
}
