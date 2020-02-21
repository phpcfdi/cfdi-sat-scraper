<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Internal\QueryResolver;
use PhpCfdi\CfdiSatScraper\Internal\SatSessionManager;
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
        $onFiveHundred = function (): void {
        };
        $sessionData = $this->createMock(SatSessionData::class);
        $httpGateway = $this->createMock(SatHttpGateway::class);
        $scraper = new class($sessionData, $httpGateway, $onFiveHundred) extends SatScraper {
            public function createQueryResolver(): QueryResolver
            {
                static $queryResolver = null;
                if (null === $queryResolver) {
                    $queryResolver = parent::createQueryResolver();
                }
                return $queryResolver;
            }
        };
        $downloader = $scraper->metadataDownloader();
        $this->assertSame($scraper->createQueryResolver(), $downloader->getQueryResolver());
        $this->assertSame($scraper->getOnFiveHundred(), $downloader->getOnFiveHundred());
    }

    public function testXmlDownloaderWithArguments(): void
    {
        $medatadaList = new MetadataList([]);
        $concurrency = 100;
        $scraper = new SatScraper($this->createMock(SatSessionData::class), $this->createMock(SatHttpGateway::class));
        $downloader = $scraper->xmlDownloader($medatadaList, $concurrency);
        $this->assertSame($concurrency, $downloader->getConcurrency());
        $this->assertSame($medatadaList, $downloader->getMetadataList());
    }

    public function testXmlDownloaderDefaults(): void
    {
        $scraper = new SatScraper($this->createMock(SatSessionData::class), $this->createMock(SatHttpGateway::class));
        $downloader = $scraper->xmlDownloader();
        $this->assertSame(XmlDownloader::DEFAULT_CONCURRENCY, $downloader->getConcurrency());
        $this->assertFalse($downloader->hasMetadataList());
    }

    public function testCreateSessionManagerIsCreatedWithCorrectProperties(): void
    {
        $sessionData = $this->createMock(SatSessionData::class);
        $httpGateway = $this->createMock(SatHttpGateway::class);
        $scraper = new class($sessionData, $httpGateway) extends SatScraper {
            public function createSessionManager(): SatSessionManager
            {
                return parent::createSessionManager();
            }
        };
        $sessionManager = $scraper->createSessionManager();
        $this->assertSame($sessionData, $sessionManager->getSessionData());
        $this->assertSame($httpGateway, $sessionManager->getHttpGateway());
    }

    public function testCreateCreateQueryResolverIsCreatedWithCorrectProperties(): void
    {
        $scraper = new class($this->createMock(SatSessionData::class)) extends SatScraper {
            public function createQueryResolver(): QueryResolver
            {
                return parent::createQueryResolver();
            }
        };
        $queryResolver = $scraper->createQueryResolver();
        $this->assertSame($scraper->getSatHttpGateway(), $queryResolver->getSatHttpGateway());
    }
}
