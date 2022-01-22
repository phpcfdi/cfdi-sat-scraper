<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Sessions\Fiel;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\Sessions\Fiel\FielLoginException;
use PhpCfdi\CfdiSatScraper\Sessions\Fiel\FielSessionManager;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class FielSessionManagerTest extends TestCase
{
    use CreateFakeFielTrait;

    public function testCreateUsesCorrectSessionData(): void
    {
        $fiel = $this->createFakeFiel();
        $manager = FielSessionManager::create($fiel);
        $this->assertSame($fiel, $manager->getSessionData()->getFiel());
    }

    public function testHasLoginWithEmptyCookie(): void
    {
        $fiel = $this->createFakeFiel();
        $manager = FielSessionManager::create($fiel);

        /** @var SatHttpGateway&MockObject $httpGateway */
        $httpGateway = $this->createMock(SatHttpGateway::class);
        $httpGateway->expects($this->once())->method('isCookieJarEmpty')->willReturn(true);

        $manager->setHttpGateway($httpGateway);
        $this->assertFalse($manager->hasLogin());
    }

    public function testHasLoginWithConnectionError(): void
    {
        $fiel = $this->createFakeFiel();
        $manager = FielSessionManager::create($fiel);

        /** @var SatHttpGateway&MockObject $httpGateway */
        $httpGateway = $this->createMock(SatHttpGateway::class);
        $httpGateway->expects($this->once())->method('isCookieJarEmpty')->willReturn(false);
        $httpGateway->expects($this->once())->method('getPortalMainPage')
            ->willThrowException($this->createMock(SatHttpGatewayException::class));

        $manager->setHttpGateway($httpGateway);
        $this->assertFalse($manager->hasLogin());
    }

    public function testHasLoginWithoutExpectedAuthenticatedMessage(): void
    {
        $fiel = $this->createFakeFiel();
        $manager = FielSessionManager::create($fiel);

        /** @var SatHttpGateway&MockObject $httpGateway */
        $httpGateway = $this->createMock(SatHttpGateway::class);
        $httpGateway->expects($this->once())->method('isCookieJarEmpty')->willReturn(false);
        $httpGateway->expects($this->once())->method('getPortalMainPage')->willReturn('<root/>');

        $manager->setHttpGateway($httpGateway);
        $this->assertFalse($manager->hasLogin());
    }

    public function testHasLoginWithExpectedAuthenticatedMessage(): void
    {
        $fiel = $this->createFakeFiel();
        $manager = FielSessionManager::create($fiel);

        /** @var SatHttpGateway&MockObject $httpGateway */
        $httpGateway = $this->createMock(SatHttpGateway::class);
        $httpGateway->expects($this->once())->method('isCookieJarEmpty')->willReturn(false);
        $httpGateway->expects($this->once())->method('getPortalMainPage')
            ->willReturn('<p>RFC Autenticado: EKU9003173C9</p>');

        $manager->setHttpGateway($httpGateway);
        $this->assertTrue($manager->hasLogin());
    }

    public function testLoginWithConnectionError(): void
    {
        $fiel = $this->createFakeFiel();
        $manager = FielSessionManager::create($fiel);

        /** @var SatHttpGateway&MockObject $httpGateway */
        $httpGateway = $this->createMock(SatHttpGateway::class);
        $httpGateway->expects($this->once())->method('getPortalMainPage')
            ->willThrowException($this->createMock(SatHttpGatewayException::class));

        $manager->setHttpGateway($httpGateway);
        $this->expectException(FielLoginException::class);
        $this->expectExceptionMessage('Connection error when try to login using FIEL');
        $manager->login();
    }

    public function testLoginProcess(): void
    {
        $fiel = $this->createFakeFiel();
        $manager = FielSessionManager::create($fiel);
        $preparedResponses = new MockHandler([
            // portal main page
            new Response(200, ['Content-type' => 'text/html'], 'no-empty'),
            // post login data
            new Response(200, ['Content-type' => 'text/html'], 'no-empty'),
            // change to fiel login page
            new Response(200, ['Content-type' => 'text/html'], $this->fileContentPath('sample-response-fiel-login-form.html')),
            // an autosubmit form with session data
            new Response(200, ['Content-type' => 'text/html'], $this->fileContentPath('sample-response-fiel-login-session.html')),
            // submit login credentials to portalcfdi
            new Response(200, ['Content-type' => 'text/html'], 'no-empty'),
        ]);
        $client = new GuzzleClient(['handler' => HandlerStack::create($preparedResponses)]);

        $httpGateway = new SatHttpGateway($client);
        $manager->setHttpGateway($httpGateway);

        $manager->login();

        $this->assertSame(0, $preparedResponses->count(), 'All responses where consumed');
    }
}
