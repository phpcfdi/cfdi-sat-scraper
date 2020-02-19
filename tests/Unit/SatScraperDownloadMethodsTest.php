<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Internal\MetadataDownloader;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\Query;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\SatSessionData;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class SatScraperDownloadMethodsTest extends TestCase
{
    public function testCreateMetadataDownloaderHasSatScraperProperties(): void
    {
        $callable = function (\DateTimeImmutable $date): void {
        };
        $satHttpGateway = $this->createMock(SatHttpGateway::class);
        $captcha = $this->createMock(CaptchaResolverInterface::class);
        $scraper = new SatScraper(new SatSessionData('rfc', 'ciec', $captcha), $satHttpGateway, $callable);
        $downloader = $scraper->createMetadataDownloader();

        $this->assertSame($callable, $downloader->getOnFiveHundred());
    }

    public function testDownloadListUuidCallDownloaderMethod(): void
    {
        /** @var SatScraper&MockObject $scraper */
        $scraper = $this->getMockBuilder(SatScraper::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['downloadListUUID'])
            ->getMock();

        // downloadListUUID must call confirmSessionIsAlive once
        $scraper->expects($a = $this->once())->method('confirmSessionIsAlive');

        // downloadListUUID must call createMetadataDownloader once
        $metadataDownloader = $this->createMock(MetadataDownloader::class);
        $scraper->expects($b = $this->once())->method('createMetadataDownloader')->willReturn($metadataDownloader);

        // MetadataDownloader::downloadByUuids must be called once
        $metadataDownloader->expects($c = $this->once())->method('downloadByUuids')->willReturn(new MetadataList([]));

        $scraper->downloadListUUID([], DownloadTypesOption::emitidos());
        $this->assertTrue($a->hasBeenInvoked());
        $this->assertTrue($b->hasBeenInvoked());
        $this->assertTrue($c->hasBeenInvoked());
    }

    public function testDownloadPeriodCallDownloaderMethod(): void
    {
        /** @var SatScraper&MockObject $scraper */
        $scraper = $this->getMockBuilder(SatScraper::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['downloadPeriod'])
            ->getMock();

        // downloadListUUID must call confirmSessionIsAlive once
        $scraper->expects($a = $this->once())->method('confirmSessionIsAlive');

        // downloadListUUID must call createMetadataDownloader once
        $metadataDownloader = $this->createMock(MetadataDownloader::class);
        $scraper->expects($b = $this->once())->method('createMetadataDownloader')->willReturn($metadataDownloader);

        // MetadataDownloader::downloadByDate must be called once
        $metadataDownloader->expects($c = $this->once())->method('downloadByDate')->willReturn(new MetadataList([]));

        $scraper->downloadPeriod($this->createMock(Query::class));
        $this->assertTrue($a->hasBeenInvoked());
        $this->assertTrue($b->hasBeenInvoked());
        $this->assertTrue($c->hasBeenInvoked());
    }

    public function testDownloadByDateTimeCallDownloaderMethod(): void
    {
        /** @var SatScraper&MockObject $scraper */
        $scraper = $this->getMockBuilder(SatScraper::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['downloadByDateTime'])
            ->getMock();

        // downloadListUUID must call confirmSessionIsAlive once
        $scraper->expects($a = $this->once())->method('confirmSessionIsAlive');

        // downloadListUUID must call createMetadataDownloader once
        $metadataDownloader = $this->createMock(MetadataDownloader::class);
        $scraper->expects($b = $this->once())->method('createMetadataDownloader')->willReturn($metadataDownloader);

        // MetadataDownloader::downloadByDateTime must be called once
        $metadataDownloader->expects($c = $this->once())->method('downloadByDateTime')->willReturn(new MetadataList([]));

        $scraper->downloadByDateTime($this->createMock(Query::class));
        $this->assertTrue($a->hasBeenInvoked());
        $this->assertTrue($b->hasBeenInvoked());
        $this->assertTrue($c->hasBeenInvoked());
    }
}
