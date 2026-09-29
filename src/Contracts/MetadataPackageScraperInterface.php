<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Exceptions\MetadataPackageException;
use PhpCfdi\CfdiSatScraper\Exceptions\PackageIsNotAvailableException;
use PhpCfdi\CfdiSatScraper\MetadataPackage\AvailablePackageList;
use PhpCfdi\CfdiSatScraper\MetadataPackage\QueryResults;
use PhpCfdi\CfdiSatScraper\QueryByFilters;

interface MetadataPackageScraperInterface
{
    /**
     * Request the generation of the metadata package for the given query.
     *
     * The period is normalized to whole months: the start date is set to the first day of month at 00:00:00
     * and the end date is set to the last day of month 23:59:59.
     *
     * @throws MetadataPackageException when the search has no results or the portal rejects the request
     */
    public function requestByMonths(QueryByFilters $query): QueryResults;

    /**
     * Request the generation of the metadata package for the given query.
     *
     * The period is normalized to whole days: the start date is set to 00:00:00
     * and the end date is set to 23:59:59.
     *
     * @throws MetadataPackageException when the search has no results or the portal rejects the request
     */
    public function requestByPeriod(QueryByFilters $query): QueryResults;

    /**
     * Request the generation of the metadata package for the given query,
     * using the exact dates and times of the query.
     *
     * When the download type is "recibidos" and the period spans more than one day,
     * one package per day or whole month is requested since the portal does not
     * a date range.
     *
     * @throws MetadataPackageException when the search has no results or the portal rejects the request
     */
    public function requestByDateTime(QueryByFilters $query): QueryResults;

    /**
     * List the packages that are ready to be downloaded (visible on the portal, last 3 days)
     */
    public function listAvailablePackages(): AvailablePackageList;

    /**
     * Download the ZIP file of a package
     * Every time this method is invoked the list of available packages is retrieved
     * For multiple packages download use downloadPackageFromAvailable.
     *
     * @see downloadPackageFromAvailable()
     * @throws PackageIsNotAvailableException when the package is not available
     * @throws MetadataPackageException when the file cannot be written
     */
    public function downloadPackage(string $uuid, string $destinationPath): void;

    /**
     * Download the ZIP file of a package
     * Should provide a recent list of packages available.
     *
     * @throws PackageIsNotAvailableException when the package is not available
     * @throws MetadataPackageException when the file cannot be written
     */
    public function downloadPackageFromAvailable(AvailablePackageList $available, string $uuid, string $destinationPath): void;

    /**
     * Initializes session on SAT
     *
     * @throws LoginException if session is not alive
     */
    public function confirmSessionIsAlive(): self;
}
