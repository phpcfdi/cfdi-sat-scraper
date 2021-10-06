<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Internal\MetadataDownloader;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\CiecSessionData;
use PhpCfdi\CfdiSatScraper\Sessions\CiecSessionManager;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class SatScraperDownloadMethodsTest extends TestCase
{
    public function testCreationOfMetadataDownloaderHasSatScraperProperties(): void
    {
        $callable = function (DateTimeImmutable $date): void {
        };
        $satHttpGateway = $this->createMock(SatHttpGateway::class);
        $captcha = $this->createMock(CaptchaResolverInterface::class);
        $sessionManager = new CiecSessionManager(new CiecSessionData('rfc', 'ciec', $captcha));
        $scraper = new SatScraper($sessionManager, $satHttpGateway, $callable);
        $downloader = $scraper->metadataDownloader();

        $this->assertSame($callable, $downloader->getOnFiveHundred());
    }

    public function testListByUuidsCallDownloaderMethod(): void
    {
        /** @var SatScraper&MockObject $scraper */
        $scraper = $this->getMockBuilder(SatScraper::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['listByUuids'])
            ->getMock();

        // listByUuids must call confirmSessionIsAlive once
        $scraper->expects($a = $this->once())->method('confirmSessionIsAlive');

        // listByUuids must call metadataDownloader once
        $metadataDownloader = $this->createMock(MetadataDownloader::class);
        $scraper->expects($b = $this->once())->method('metadataDownloader')->willReturn($metadataDownloader);

        // MetadataDownloader::downloadByUuids must be called once
        $metadataDownloader->expects($c = $this->once())->method('downloadByUuids')->willReturn(new MetadataList([]));

        $scraper->listByUuids([], DownloadType::emitidos());
        $this->assertTrue($a->hasBeenInvoked());
        $this->assertTrue($b->hasBeenInvoked());
        $this->assertTrue($c->hasBeenInvoked());
    }

    public function testListByPeriodCallDownloaderMethod(): void
    {
        /** @var SatScraper&MockObject $scraper */
        $scraper = $this->getMockBuilder(SatScraper::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['listByPeriod'])
            ->getMock();

        // listByPeriod must call confirmSessionIsAlive once
        $scraper->expects($a = $this->once())->method('confirmSessionIsAlive');

        // listByPeriod must call metadataDownloader once
        $metadataDownloader = $this->createMock(MetadataDownloader::class);
        $scraper->expects($b = $this->once())->method('metadataDownloader')->willReturn($metadataDownloader);

        // MetadataDownloader::downloadByDate must be called once
        $metadataDownloader->expects($c = $this->once())->method('downloadByDate')->willReturn(new MetadataList([]));

        $scraper->listByPeriod($this->createMock(QueryByFilters::class));
        $this->assertTrue($a->hasBeenInvoked());
        $this->assertTrue($b->hasBeenInvoked());
        $this->assertTrue($c->hasBeenInvoked());
    }

    public function testListByDateTimeCallDownloaderMethod(): void
    {
        /** @var SatScraper&MockObject $scraper */
        $scraper = $this->getMockBuilder(SatScraper::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['listByDateTime'])
            ->getMock();

        // listByDateTime must call confirmSessionIsAlive once
        $scraper->expects($a = $this->once())->method('confirmSessionIsAlive');

        // listByDateTime must call metadataDownloader once
        $metadataDownloader = $this->createMock(MetadataDownloader::class);
        $scraper->expects($b = $this->once())->method('metadataDownloader')->willReturn($metadataDownloader);

        // MetadataDownloader::downloadByDateTime must be called once
        $metadataDownloader->expects($c = $this->once())->method('downloadByDateTime')->willReturn(new MetadataList([]));

        $scraper->listByDateTime($this->createMock(QueryByFilters::class));
        $this->assertTrue($a->hasBeenInvoked());
        $this->assertTrue($b->hasBeenInvoked());
        $this->assertTrue($c->hasBeenInvoked());
    }

    public function testConfirmSessionIsAlive(): void
    {
        /** @var SatHttpGateway&MockObject $httpGateway */
        $httpGateway = $this->createMock(SatHttpGateway::class);

        /** @var SessionManager&MockObject $sessionManager */
        $sessionManager = $this->createMock(SessionManager::class);
        $sessionManager->expects($this->once())->method('setHttpGateway')->with($httpGateway);
        $sessionManager->expects($this->once())->method('hasLogin')->willReturn(true);
        $sessionManager->expects($this->once())->method('registerOnPortalMainPage');

        // prepare a scraper with custom session manager
        $scraper = new SatScraper($sessionManager, $httpGateway);

        $this->assertSame($scraper, $scraper->confirmSessionIsAlive(), 'confirmSessionIsAlive is a fluent method');
    }
}
