<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Contracts\MetadataMessageHandler;
use PhpCfdi\CfdiSatScraper\NullMetadataMessageHandler;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class SatScraperPropertiesTest extends TestCase
{
    private function createSatScraper(
        ?SessionManager $sessionManager = null,
        ?SatHttpGateway $satHttpGateway = null,
        ?MetadataMessageHandler $messageHandler = null,
    ): SatScraper {
        return new SatScraper(
            $sessionManager ?? $this->createMock(SessionManager::class),
            $satHttpGateway,
            $messageHandler,
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

    public function testMetadataMessageHandler(): void
    {
        $handler = $this->createMock(MetadataMessageHandler::class);
        $scraper = $this->createSatScraper(null, null, $handler);
        $this->assertSame($handler, $scraper->getMetadataMessageHandler(), 'Given handler should be the same');
    }

    public function testMetadataMessageHandlerDefault(): void
    {
        $scraper = $this->createSatScraper();
        $this->assertInstanceOf(NullMetadataMessageHandler::class, $scraper->getMetadataMessageHandler());
    }
}
