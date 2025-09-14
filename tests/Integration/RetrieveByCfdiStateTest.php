<?php

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Integration;

use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PHPUnit\Framework\Attributes\DataProvider;

class RetrieveByCfdiStateTest extends IntegrationTestCase
{
    #[DataProvider('providerEmitidosRecibidos')]
    public function testRetrieveByCfdiStateCancelados(DownloadType $downloadType): void
    {
        $state = StatesVoucherOption::cancelados();
        $typeText = $this->getDownloadTypeText($downloadType);
        $repository = $this->getRepository()->filterByType($downloadType);
        $repository = $repository->filterByState($state);
        if (0 === $repository->count()) {
            $this->markTestSkipped(
                sprintf('The repository does not have CFDI %s with state Cancelado', $typeText),
            );
        }

        $scraper = $this->getSatScraper();
        $query = (new QueryByFilters($repository->getSinceDate(), $repository->getUntilDate()))
            ->setDownloadType($downloadType)
            ->setStateVoucher($state);
        $list = $scraper->listByDateTime($query);

        $this->assertRepositoryEqualsMetadataList($repository, $list);
    }

    #[DataProvider('providerEmitidosRecibidos')]
    public function testRetrieveByCfdiStateVigentes(DownloadType $downloadType): void
    {
        $state = StatesVoucherOption::vigentes();
        $typeText = $this->getDownloadTypeText($downloadType);
        $repository = $this->getRepository()->filterByType($downloadType);
        $repository = $repository->filterByState($state);
        if (0 === $repository->count()) {
            $this->markTestSkipped(
                sprintf('The repository does not have CFDI %s with state Vigente', $typeText),
            );
        }

        $scraper = $this->getSatScraper();
        $query = (new QueryByFilters($repository->getSinceDate(), $repository->getUntilDate()))
            ->setDownloadType($downloadType)
            ->setStateVoucher($state);
        $list = $scraper->listByDateTime($query);

        $this->assertRepositoryEqualsMetadataList($repository, $list);
    }
}
