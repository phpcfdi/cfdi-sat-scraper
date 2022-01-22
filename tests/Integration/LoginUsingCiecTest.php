<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Integration;

use Throwable;

final class LoginUsingCiecTest extends IntegrationTestCase
{
    public function testLoginAndLogout(): void
    {
        $factory = $this->getFactory();

        try {
            $sessionManager = $factory->createCiecSessionManager();
        } catch (Throwable $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

        $scraper = $factory->createSatScraper($sessionManager);
        $sessionManager->setHttpGateway($scraper->getSatHttpGateway());

        $scraper->confirmSessionIsAlive();
        $this->assertTrue($sessionManager->hasLogin());

        if ('CIEC' !== $factory->env('SAT_AUTH_MODE')) {
            // do not log out if SAT_AUTH_MODE is CIEC, to avoid double captcha resolution
            $sessionManager->logout();
            $this->assertFalse($sessionManager->hasLogin());
        }
    }
}
