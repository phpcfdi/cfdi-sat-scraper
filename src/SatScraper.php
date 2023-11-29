<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use LogicException;
use PhpCfdi\CfdiSatScraper\Contracts\MaximumRecordsHandler;
use PhpCfdi\CfdiSatScraper\Contracts\MetadataMessageHandler;
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

    /**
     * @var MaximumRecordsHandler
     * @deprecated 3.4.0
     */
    protected $maximumRecordsHandler;

    /** @var MetadataMessageHandler */
    protected $metadataMessageHandler;

    /**
     * SatScraper constructor.
     *
     * @param SessionManager $sessionManager
     * @param SatHttpGateway|null $satHttpGateway
     * @param MetadataMessageHandler|MaximumRecordsHandler|null $maximumRecordsHandler
     */
    public function __construct(
        SessionManager $sessionManager,
        ?SatHttpGateway $satHttpGateway = null,
        $maximumRecordsHandler = null
    ) {
        if ($maximumRecordsHandler instanceof MetadataMessageHandler) {
            $metadataMessageHandler = $maximumRecordsHandler;
            $maximumRecordsHandler = new Internal\MetadataMessageHandlerWrapper($maximumRecordsHandler);
        } elseif ($maximumRecordsHandler instanceof MaximumRecordsHandler) {
            trigger_error(
                sprintf(
                    <<< 'MESSAGE'
                        Class %1$s with argument $maximumRecordsHandler of type MaximumRecordsHandler is deprecated,
                        use a MetadataMessageHandler implementation.
                        MESSAGE,
                    self::class,
                ),
                E_USER_DEPRECATED,
            );
            $metadataMessageHandler = new Internal\MaximumRecordsHandlerWrapper($maximumRecordsHandler);
        } elseif (null === $maximumRecordsHandler) {
            $maximumRecordsHandler = new NullMaximumRecordsHandler();
            $metadataMessageHandler = new NullMetadataMessageHandler();
        } else {
            throw new LogicException('Invalid parameter type maximumRecordsHandler');
        }

        $this->sessionManager = $sessionManager;
        $this->satHttpGateway = $satHttpGateway ?? $this->createDefaultSatHttpGateway();
        $this->maximumRecordsHandler = $maximumRecordsHandler;
        $this->metadataMessageHandler = $metadataMessageHandler;
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

    /** @deprecated 3.4.0 */
    public function getMaximumRecordsHandler(): MaximumRecordsHandler
    {
        trigger_error(
            sprintf('Method %1$s::getMaximumRecordsHandler is deprecated, use %1$s::getMetadataMessageHandler', self::class),
            E_USER_DEPRECATED,
        );
        return $this->maximumRecordsHandler;
    }

    public function getMetadataMessageHandler(): MetadataMessageHandler
    {
        return $this->metadataMessageHandler;
    }
}
