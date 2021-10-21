<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Contracts\MaximumRecordsHandler;
use PhpCfdi\CfdiSatScraper\Internal\NullMaximumRecordsHandler;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class SatScraperPropertiesTest extends TestCase
{
    private function createSatScraper(
        ?SessionManager $sessionManager = null,
        ?SatHttpGateway $satHttpGateway = null,
        ?MaximumRecordsHandler $maximumRecordsHandler = null
    ): SatScraper {
        return new SatScraper(
            $sessionManager ?? $this->createMock(SessionManager::class),
            $satHttpGateway,
            $maximumRecordsHandler,
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

    public function testMaximumRecordsHandler(): void
    {
        $handler = $this->createMock(MaximumRecordsHandler::class);
        $scraper = $this->createSatScraper(null, null, $handler);
        $this->assertSame($handler, $scraper->getMaximumRecordsHandler(), 'Given handler should be the same');
    }

    public function testMaximumRecordsHandlerDefault(): void
    {
        $scraper = $this->createSatScraper();
        $this->assertInstanceOf(NullMaximumRecordsHandler::class, $scraper->getMaximumRecordsHandler());
    }
}
