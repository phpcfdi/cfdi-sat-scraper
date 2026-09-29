<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Contracts\MetadataPackageScraperInterface;
use PhpCfdi\CfdiSatScraper\Internal\CommonMethodsScraperTrait;
use PhpCfdi\CfdiSatScraper\Internal\MetadataPackageRequester;
use PhpCfdi\CfdiSatScraper\MetadataPackage\AvailablePackageList;
use PhpCfdi\CfdiSatScraper\MetadataPackage\QueryResults;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;

class MetadataPackageScraper implements MetadataPackageScraperInterface
{
    use CommonMethodsScraperTrait;

    private readonly MetadataPackageRequester $requester;

    public function __construct(
        SessionManager $sessionManager,
        ?SatHttpGateway $satHttpGateway = null,
        ?MetadataPackageRequester $requester = null,
    ) {
        $this->sessionManager = $sessionManager;
        $this->satHttpGateway = $satHttpGateway ?? $this->createDefaultSatHttpGateway();
        $this->requester = $requester ?? new MetadataPackageRequester($this->satHttpGateway);
    }

    public function requestByMonths(QueryByFilters $query): QueryResults
    {
        $startDate = $query->getStartDate()->modify('first day of this month 00:00:00');
        $endDate = $query->getEndDate()->modify('last day of this month 23:59:59');

        $query = clone $query;
        $query->setPeriod($startDate, $endDate);

        return $this->requestByDateTime($query);
    }

    public function requestByPeriod(QueryByFilters $query): QueryResults
    {
        $startDate = $query->getStartDate()->setTime(0, 0, 0);
        $endDate = $query->getEndDate()->setTime(23, 59, 59);

        $query = clone $query;
        $query->setPeriod($startDate, $endDate);

        return $this->requestByDateTime($query);
    }

    public function requestByDateTime(QueryByFilters $query): QueryResults
    {
        $this->confirmSessionIsAlive();
        return $this->requester->requestPackages($query);
    }

    public function listAvailablePackages(): AvailablePackageList
    {
        $this->confirmSessionIsAlive();
        return $this->requester->listAvailablePackages();
    }

    public function downloadPackage(string $uuid, string $destinationPath): void
    {
        // do not check that session is alive, the next call will do
        $available = $this->listAvailablePackages();
        $this->downloadPackageFromAvailable($available, $uuid, $destinationPath);
    }

    public function downloadPackageFromAvailable(AvailablePackageList $available, string $uuid, string $destinationPath): void
    {
        // do not check that session is alive, it should be shen check for available packages
        $this->requester->downloadPackageFromAvailable($available, $uuid, $destinationPath);
    }
}
