<?php

/**
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Contracts\MaximumRecordsHandler;
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
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;

final class SatScraperDownloadMethodsTest extends TestCase
{
    public function testCreationOfMetadataDownloaderHasSatScraperProperties(): void
    {
        $handler = $this->createMock(MaximumRecordsHandler::class);
        $satHttpGateway = $this->createMock(SatHttpGateway::class);
        $captcha = $this->createMock(CaptchaResolverInterface::class);
        $sessionManager = new CiecSessionManager(new CiecSessionData('rfc', 'ciec', $captcha));
        $scraper = new class ($sessionManager, $satHttpGateway, $handler) extends SatScraper {
            public function exposeCreateMetadataDownloader(): MetadataDownloader
            {
                return $this->createMetadataDownloader();
            }
        };
        $downloader = $scraper->exposeCreateMetadataDownloader();

        $this->assertSame($handler, $downloader->getMaximumRecordsHandler());
    }

    /** @see SatScraper::listByUuids */
    public function testListByUuidsCallDownloaderMethod(): void
    {
        /** @var SatScraper&MockObject $scraper */
        $scraper = $this->getMockBuilder(SatScraper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['confirmSessionIsAlive', 'createMetadataDownloader'])
            ->getMock();

        // listByUuids must call confirmSessionIsAlive once
        $scraper->expects($this->once())->method('confirmSessionIsAlive');

        // listByUuids must call metadataDownloader once
        $metadatalist = $this->createMock(MetadataList::class);
        $metadataDownloader = $this->createMock(MetadataDownloader::class);
        $scraper->expects($this->once())->method('createMetadataDownloader')->willReturn($metadataDownloader);

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
            ->onlyMethods(['confirmSessionIsAlive', 'createMetadataDownloader'])
            ->getMock();

        // listByPeriod must call confirmSessionIsAlive once
        $scraper->expects($this->once())->method('confirmSessionIsAlive');

        // listByPeriod must call metadataDownloader once
        $metadataDownloader = $this->createMock(MetadataDownloader::class);
        $scraper->expects($this->once())->method('createMetadataDownloader')->willReturn($metadataDownloader);

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
            ->onlyMethods(['confirmSessionIsAlive', 'createMetadataDownloader'])
            ->getMock();

        // listByDateTime must call confirmSessionIsAlive once
        $scraper->expects($this->once())->method('confirmSessionIsAlive');

        // listByDateTime must call metadataDownloader once
        $metadataDownloader = $this->createMock(MetadataDownloader::class);
        $scraper->expects($this->once())->method('createMetadataDownloader')->willReturn($metadataDownloader);

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
        // hasLogin will return true, so login should not be called
        $sessionManager->expects($this->once())->method('hasLogin')->willReturn(true);
        $sessionManager->expects($loginSpy = $this->any())->method('login');

        // prepare a scraper with custom session manager
        $scraper = new SatScraper($sessionManager, $this->createMock(SatHttpGateway::class));
        $scraper->confirmSessionIsAlive();

        $this->assertFalse($loginSpy->hasBeenInvoked());
    }
}
