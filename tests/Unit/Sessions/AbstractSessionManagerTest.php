<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Sessions;

use LogicException;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\Sessions\AbstractSessionManager;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class AbstractSessionManagerTest extends TestCase
{
    public function testHttpGatewayGetWithoutInstance(): void
    {
        $stub = $this->getMockForAbstractClass(AbstractSessionManager::class);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Must set http gateway property before use');
        $stub->getHttpGateway();
    }

    public function testHttpGatewaySetAndGet(): void
    {
        $httpGateway = $this->createMock(SatHttpGateway::class);
        $stub = $this->getMockForAbstractClass(AbstractSessionManager::class);
        $stub->setHttpGateway($httpGateway);
        $this->assertSame($httpGateway, $stub->getHttpGateway());
    }
}
