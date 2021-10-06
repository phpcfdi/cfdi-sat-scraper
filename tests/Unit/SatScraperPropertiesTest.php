<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class SatScraperPropertiesTest extends TestCase
{
    private function createSatScraper(?SessionManager $sessionManager = null, ?SatHttpGateway $satHttpGateway = null, ?callable $onFiveHundred = null): SatScraper
    {
        return new SatScraper(
            $sessionManager ?? $this->createMock(SessionManager::class),
            $satHttpGateway,
            $onFiveHundred
        );
    }

    private function createSatHttpGateway(): SatHttpGateway
    {
        return new SatHttpGateway();
    }

    public function testSessionManagerProperty(): void
    {
        $sessionManager = $this->createMock(SessionManager::class);
        $scraper = $this->createSatScraper($sessionManager);
        $this->assertSame($sessionManager, $scraper->getSessionManager());
    }

    public function testSatHttpGatewayProperty(): void
    {
        $satHttpGateway = $this->createSatHttpGateway();
        $scraper = $this->createSatScraper(null, $satHttpGateway);
        $this->assertSame($satHttpGateway, $scraper->getSatHttpGateway());
    }

    public function testSatHttpGatewayDefault(): void
    {
        $scraper = $this->createSatScraper();
        $this->assertInstanceOf(SatHttpGateway::class, $scraper->getSatHttpGateway());
    }

    public function testOnFiveHundred(): void
    {
        $callable = function (): void {
        };
        $scraper = $this->createSatScraper(null, null, $callable);
        $this->assertSame($callable, $scraper->getOnFiveHundred(), 'Given OnFiveHundred was not the same');
    }

    public function testOnFiveHundredDefault(): void
    {
        $scraper = $this->createSatScraper();
        $this->assertNull($scraper->getOnFiveHundred(), 'Default OnFiveHundred should be NULL');
    }
}
