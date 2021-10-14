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
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionData;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionManager;
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

    /** @see SatScraper::listByUuids */
    public function testListByUuidsCallDownloaderMethod(): void
    {
        /** @var SatScraper&MockObject $scraper */
        $scraper = $this->getMockBuilder(SatScraper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['confirmSessionIsAlive', 'metadataDownloader'])
            ->getMock();

        // listByUuids must call confirmSessionIsAlive once
        $scraper->expects($this->once())->method('confirmSessionIsAlive');

        // listByUuids must call metadataDownloader once
        $metadatalist = $this->createMock(MetadataList::class);
        $metadataDownloader = $this->createMock(MetadataDownloader::class);
        $scraper->expects($this->once())->method('metadataDownloader')->willReturn($metadataDownloader);

        // MetadataDownloader::downloadByUuids must be called once
        $uuids = [];
        $downloadType = DownloadType::emitidos();
        $metadataDownloader->expects($this->once())
            ->method('downloadByUuids')
            ->with($uuids, $downloadType)
            ->willReturn($metadatalist);

        $this->assertSame($metadatalist, $scraper->listByUuids($uuids, $downloadType));
    }

    public function testListByPeriodCallDownloaderMethod(): void
    {

        /** @var SatScraper&MockObject $scraper */
        $scraper = $this->getMockBuilder(SatScraper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['confirmSessionIsAlive', 'metadataDownloader'])
            ->getMock();

        // listByPeriod must call confirmSessionIsAlive once
        $scraper->expects($this->once())->method('confirmSessionIsAlive');

        // listByPeriod must call metadataDownloader once
        $metadataDownloader = $this->createMock(MetadataDownloader::class);
        $scraper->expects($this->once())->method('metadataDownloader')->willReturn($metadataDownloader);

        // MetadataDownloader::downloadByDate must be called once
        $query = $this->createMock(QueryByFilters::class);
        $metadatalist = $this->createMock(MetadataList::class);
        $metadataDownloader->expects($this->once())
            ->method('downloadByDate')
            ->with($query)
            ->willReturn($metadatalist);

        $this->assertSame($metadatalist, $scraper->listByPeriod($query));
    }

    public function testListByDateTimeCallDownloaderMethod(): void
    {
        /** @var SatScraper&MockObject $scraper */
        $scraper = $this->getMockBuilder(SatScraper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['confirmSessionIsAlive', 'metadataDownloader'])
            ->getMock();

        // listByDateTime must call confirmSessionIsAlive once
        $scraper->expects($this->once())->method('confirmSessionIsAlive');

        // listByDateTime must call metadataDownloader once
        $metadataDownloader = $this->createMock(MetadataDownloader::class);
        $scraper->expects($this->once())->method('metadataDownloader')->willReturn($metadataDownloader);

        // MetadataDownloader::downloadByDateTime must be called once
        $query = $this->createMock(QueryByFilters::class);
        $metadataList = $this->createMock(MetadataList::class);
        $metadataDownloader->expects($this->once())
            ->method('downloadByDateTime')
            ->with($query)
            ->willReturn($metadataList);

        $this->assertSame($metadataList, $scraper->listByDateTime($query));
    }

    public function testConfirmSessionIsAliveCallsLogin(): void
    {
        /** @var SatHttpGateway&MockObject $httpGateway */
        $httpGateway = $this->createMock(SatHttpGateway::class);

        /** @var SessionManager&MockObject $sessionManager */
        $sessionManager = $this->createMock(SessionManager::class);
        $sessionManager->expects($this->once())->method('setHttpGateway')->with($httpGateway);
        $sessionManager->expects($this->once())->method('hasLogin')->willReturn(false);
        $sessionManager->expects($this->once())->method('login');
        $sessionManager->expects($this->once())->method('accessPortalMainPage');

        // prepare a scraper with custom session manager
        $scraper = new SatScraper($sessionManager, $httpGateway);

        $this->assertSame($scraper, $scraper->confirmSessionIsAlive(), 'confirmSessionIsAlive is a fluent method');
    }

    public function testConfirmSessionIsAliveLoggedInNoCallsLogin(): void
    {
        /** @var SessionManager&MockObject $sessionManager */
        $sessionManager = $this->createMock(SessionManager::class);
        // hasLogin will return true, so login should not me called
        $sessionManager->expects($this->once())->method('hasLogin')->willReturn(true);
        $sessionManager->expects($loginSpy = $this->any())->method('login');

        // prepare a scraper with custom session manager
        $scraper = new SatScraper($sessionManager, $this->createMock(SatHttpGateway::class));
        $scraper->confirmSessionIsAlive();

        $this->assertFalse($loginSpy->hasBeenInvoked());
    }
}
