<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Integration;

use Throwable;

final class LoginUsingFielTest extends IntegrationTestCase
{
    public function testLoginAndLogout(): void
    {
        $factory = $this->getFactory();
        try {
            $sessionManager = $factory->createFielSessionManager();
        } catch (Throwable $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

        $scraper = $factory->createSatScraper($sessionManager);
        $sessionManager->setHttpGateway($scraper->getSatHttpGateway());

        $scraper->confirmSessionIsAlive();
        $this->assertTrue($sessionManager->hasLogin());

        $sessionManager->logout();
        $this->assertFalse($sessionManager->hasLogin());
    }
}
