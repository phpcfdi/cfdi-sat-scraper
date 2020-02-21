<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\SatSessionData;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PhpCfdi\CfdiSatScraper\XmlDownloader;

final class SatScraperFactoryMethodsTest extends TestCase
{
    public function testMetadataDownloader(): void
    {
        $medatadaList = new MetadataList([]);
        $concurrency = 100;
        $scraper = new SatScraper($this->createMock(SatSessionData::class), $this->createMock(SatHttpGateway::class));
        $downloader = $scraper->xmlDownloader($medatadaList, $concurrency);
        $this->assertSame($concurrency, $downloader->getConcurrency());
        $this->assertSame($medatadaList, $downloader->getMetadataList());
    }

    public function testMetadataDownloaderDefaults(): void
    {
        $scraper = new SatScraper($this->createMock(SatSessionData::class), $this->createMock(SatHttpGateway::class));
        $downloader = $scraper->xmlDownloader();
        $this->assertSame(XmlDownloader::DEFAULT_CONCURRENCY, $downloader->getConcurrency());
        $this->assertFalse($downloader->hasMetadataList());
    }
}
