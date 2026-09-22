<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Contracts\MetadataPackageScraperInterface;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\MetadataPackageScraper;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;
use PhpCfdi\CfdiSatScraper\Tests\Fakes\FakeMetadataPackageRequester;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class MetadataPackageScraperTest extends TestCase
{
    /** @var SessionManager&MockObject */
    private MockObject $sessionManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionManager = $this->createMock(SessionManager::class);
    }

    public function testInstanceImplementsInterface(): void
    {
        $scraper = new MetadataPackageScraper($this->sessionManager);

        $this->assertInstanceOf(MetadataPackageScraperInterface::class, $scraper);
    }

    public function testConstructorUsesDefaultGatewayWhenNotProvided(): void
    {
        $scraper = new MetadataPackageScraper($this->sessionManager);

        $this->assertSame($this->sessionManager, $scraper->getSessionManager());
        $this->assertInstanceOf(SatHttpGateway::class, $scraper->getSatHttpGateway());
    }

    public function testConstructorUsesProvidedGateway(): void
    {
        $gateway = new SatHttpGateway();
        $scraper = new MetadataPackageScraper($this->sessionManager, $gateway);

        $this->assertSame($gateway, $scraper->getSatHttpGateway());
    }

    public function testConfirmSessionIsAlivePerformLoginWhenNotLogged(): void
    {
        $this->sessionManager->method('hasLogin')->willReturn(false);
        $this->sessionManager->expects($this->once())->method('login');
        $this->sessionManager->expects($this->once())->method('accessPortalMainPage');

        $scraper = new MetadataPackageScraper($this->sessionManager);
        $this->assertSame($scraper, $scraper->confirmSessionIsAlive());
    }

    public function testConfirmSessionIsAliveDoesNotPerformLoginWhenLogged(): void
    {
        $this->sessionManager->method('hasLogin')->willReturn(true);
        $this->sessionManager->expects($this->never())->method('login');
        $this->sessionManager->expects($this->once())->method('accessPortalMainPage');

        $scraper = new MetadataPackageScraper($this->sessionManager);
        $scraper->confirmSessionIsAlive();
    }

    public function testGatewayCanBeSharedWithSatScraper(): void
    {
        $gateway = new SatHttpGateway();
        $satScraper = new SatScraper($this->sessionManager, $gateway);
        $scraper = new MetadataPackageScraper($this->sessionManager, $gateway);

        $this->assertSame($satScraper->getSatHttpGateway(), $scraper->getSatHttpGateway());
    }

    public function testRequestByMonthsNormalizesToWholeMonthsEmitidos(): void
    {
        $this->sessionManager->method('hasLogin')->willReturn(true);
        $requester = new FakeMetadataPackageRequester();
        $scraper = new MetadataPackageScraper($this->sessionManager, requester: $requester);

        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-01'),
            new DateTimeImmutable('2026-03-15'),
            DownloadType::emitidos(),
        );
        $packages = $scraper->requestByMonths($query);

        $captured = $requester->getCaptured();

        // the query was normalized to whole days before requesting the packages
        $this->assertCount(1, $captured);

        $result = $captured[0]['result'];
        $this->assertSame('2026-01-01 00:00:00', $result->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-03-31 23:59:59', $result->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertTrue($packages->has($result->getUuid()));
    }

    public function testRequestByMonthsNormalizesToWholeMonthsRecibidos(): void
    {
        $this->sessionManager->method('hasLogin')->willReturn(true);
        $requester = new FakeMetadataPackageRequester();
        $scraper = new MetadataPackageScraper($this->sessionManager, requester: $requester);

        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-01'),
            new DateTimeImmutable('2026-03-15'),
            DownloadType::recibidos(),
        );
        $results = $scraper->requestByMonths($query);

        $captured = $requester->getCaptured();

        // the query was normalized to whole days before requesting the packages
        $this->assertCount(3, $captured);

        $result = $captured[0]['result'];
        $this->assertSame('2026-01-01 00:00:00', $result->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-01-31 23:59:59', $result->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertTrue($results->has($result->getUuid()));

        $result = $captured[1]['result'];
        $this->assertSame('2026-02-01 00:00:00', $result->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-02-28 23:59:59', $result->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertTrue($results->has($result->getUuid()));

        $result = $captured[2]['result'];
        $this->assertSame('2026-03-01 00:00:00', $result->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-03-31 23:59:59', $result->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertTrue($results->has($result->getUuid()));
    }

    public function testRequestByPeriodNormalizesToWholeDays(): void
    {
        $this->sessionManager->method('hasLogin')->willReturn(true);
        $requester = new FakeMetadataPackageRequester();
        $scraper = new MetadataPackageScraper($this->sessionManager, requester: $requester);

        $query = new QueryByFilters(
            new DateTimeImmutable('2026-08-01'),
            new DateTimeImmutable('2026-08-07'),
        );

        $packages = $scraper->requestByPeriod($query);

        // the query was normalized to whole days before requesting the packages
        $this->assertCount(1, $requester->getCaptured());

        $captured = $requester->getCapture(0);
        $this->assertSame('2026-08-01 00:00:00', $captured['query']->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-08-07 23:59:59', $captured['query']->getEndDate()->format('Y-m-d H:i:s'));
        // the original query was not modified
        $this->assertSame('2026-08-07 00:00:00', $query->getEndDate()->format('Y-m-d H:i:s'));
        // the result exposes the normalized period
        $result = $packages->get($captured['result']->getUuid());
        $this->assertSame('2026-08-07 23:59:59', $result->getEndDate()->format('Y-m-d H:i:s'));
    }

    public function testRequestByDateTimeKeepsTheExactDatesOnEmitidos(): void
    {
        $this->sessionManager->method('hasLogin')->willReturn(true);
        $requester = new FakeMetadataPackageRequester();
        $scraper = new MetadataPackageScraper($this->sessionManager, requester: $requester);

        $query = new QueryByFilters(
            new DateTimeImmutable('2026-08-01 06:30:00'),
            new DateTimeImmutable('2026-08-31 18:45:00'),
            DownloadType::emitidos(),
        );
        $scraper->requestByDateTime($query);

        $capturedQuery = $requester->getCapture(0)['query'];
        $this->assertSame('2026-08-01 06:30:00', $capturedQuery->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-08-31 18:45:00', $capturedQuery->getEndDate()->format('Y-m-d H:i:s'));
    }
}
