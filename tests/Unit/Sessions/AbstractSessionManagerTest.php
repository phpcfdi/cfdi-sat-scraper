<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Sessions;

use LogicException;
use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\Sessions\AbstractSessionManager;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class AbstractSessionManagerTest extends TestCase
{
    private function createStubForHttpGatewayProperty(): AbstractSessionManager
    {
        return new class () extends AbstractSessionManager {
            protected function createExceptionConnection(
                string $when,
                SatHttpGatewayException $exception,
            ): LoginException {
                throw new LogicException('This is a stub');
            }

            protected function createExceptionNotAuthenticated(string $html): LoginException
            {
                throw new LogicException('This is a stub');
            }

            public function hasLogin(): bool
            {
                throw new LogicException('This is a stub');
            }

            public function login(): void
            {
                throw new LogicException('This is a stub');
            }

            public function getRfc(): string
            {
                throw new LogicException('This is a stub');
            }
        };
    }

    public function testHttpGatewayGetWithoutInstance(): void
    {
        $stub = $this->createStubForHttpGatewayProperty();
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Must set http gateway property before use');
        $stub->getHttpGateway();
    }

    public function testHttpGatewaySetAndGet(): void
    {
        $stub = $this->createStubForHttpGatewayProperty();
        $httpGateway = $this->createMock(SatHttpGateway::class);
        $stub->setHttpGateway($httpGateway);
        $this->assertSame($httpGateway, $stub->getHttpGateway());
    }
}
