<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\MetadataDownloader;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\Query;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class SatScraperDownloadMethodsTest extends TestCase
{
    public function testCreateMetadataDownloaderHasSatScraperProperties(): void
    {
        $callable = function (\DateTimeImmutable $date): void {
        };
        $client = $this->createMock(Client::class);
        $cookie = $this->createMock(CookieJar::class);
        $captcha = $this->createMock(CaptchaResolverInterface::class);
        $scraper = new SATScraper('rfc', 'ciec', $client, $cookie, $captcha);
        $scraper->setOnFiveHundred($callable);
        $downloader = $scraper->createMetadataDownloader();

        $this->assertSame($client, $downloader->getQueryResolver()->getClient());
        $this->assertSame($cookie, $downloader->getQueryResolver()->getCookie());
        $this->assertSame($callable, $downloader->getOnFiveHundred());
    }

    public function testDownloadListUuidCallDownloaderMethod(): void
    {
        /** @var SATScraper&MockObject $scraper */
        $scraper = $this->getMockBuilder(SATScraper::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['downloadListUUID'])
            ->getMock();

        // downloadListUUID must call initScraper once
        $scraper->expects($a = $this->once())->method('initScraper');

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
        /** @var SATScraper&MockObject $scraper */
        $scraper = $this->getMockBuilder(SATScraper::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['downloadPeriod'])
            ->getMock();

        // downloadListUUID must call initScraper once
        $scraper->expects($a = $this->once())->method('initScraper');

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
        /** @var SATScraper&MockObject $scraper */
        $scraper = $this->getMockBuilder(SATScraper::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['downloadByDateTime'])
            ->getMock();

        // downloadListUUID must call initScraper once
        $scraper->expects($a = $this->once())->method('initScraper');

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
