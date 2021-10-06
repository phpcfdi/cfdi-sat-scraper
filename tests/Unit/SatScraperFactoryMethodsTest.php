<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Internal\QueryResolver;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\ResourceDownloader;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class SatScraperFactoryMethodsTest extends TestCase
{
    public function testMetadataDownloader(): void
    {
        $onFiveHundred = function (): void {
        };
        $sessionManager = $this->createMock(SessionManager::class);
        $httpGateway = $this->createMock(SatHttpGateway::class);
        $scraper = new class($sessionManager, $httpGateway, $onFiveHundred) extends SatScraper {
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

    public function testResourceDownloaderWithArguments(): void
    {
        $medatadaList = new MetadataList([]);
        $concurrency = 100;
        $scraper = new SatScraper($this->createMock(SessionManager::class), $this->createMock(SatHttpGateway::class));
        $downloader = $scraper->resourceDownloader(ResourceType::xml(), $medatadaList, $concurrency);
        $this->assertSame($concurrency, $downloader->getConcurrency());
        $this->assertSame($medatadaList, $downloader->getMetadataList());
    }

    public function testResourceDownloaderDefaults(): void
    {
        $scraper = new SatScraper($this->createMock(SessionManager::class), $this->createMock(SatHttpGateway::class));
        $downloader = $scraper->resourceDownloader();
        $this->assertSame(ResourceDownloader::DEFAULT_CONCURRENCY, $downloader->getConcurrency());
        $this->assertFalse($downloader->hasMetadataList());
    }

    public function testCreateCreateQueryResolverIsCreatedWithCorrectProperties(): void
    {
        $scraper = new class($this->createMock(SessionManager::class)) extends SatScraper {
            public function createQueryResolver(): QueryResolver
            {
                return parent::createQueryResolver();
            }
        };
        $queryResolver = $scraper->createQueryResolver();
        $this->assertSame($scraper->getSatHttpGateway(), $queryResolver->getSatHttpGateway());
    }
}
