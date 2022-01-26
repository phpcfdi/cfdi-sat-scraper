<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Sessions\Ciec;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\Sessions\AbstractSessionManager;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecLoginException;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionManager;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;

final class CiecSessionManagerTest extends TestCase
{
    public function testCreateObject(): void
    {
        $rfc = 'COSC8001137NA';
        $ciec = 'CiecPassword';
        $resolver = $this->createMock(CaptchaResolverInterface::class);
        $manager = CiecSessionManager::create($rfc, $ciec, $resolver);
        $this->assertInstanceOf(AbstractSessionManager::class, $manager);
        $this->assertInstanceOf(SessionManager::class, $manager);
        $this->assertSame($rfc, $manager->getRfc());
        $this->assertSame($ciec, $manager->getSessionData()->getCiec());
        $this->assertSame($resolver, $manager->getSessionData()->getCaptchaResolver());
    }

    public function testRequestCaptchaImageWithoutCaptchaImage(): void
    {
        $rfc = 'COSC8001137NA';
        $ciec = 'CiecPassword';
        $resolver = $this->createMock(CaptchaResolverInterface::class);
        $manager = CiecSessionManager::create($rfc, $ciec, $resolver);

        $preparedResponses = new MockHandler([
            // get regular login page
            new Response(200, ['Content-type' => 'text/html'], '<p>no content</p>'),
        ]);
        $client = new GuzzleClient(['handler' => HandlerStack::create($preparedResponses)]);

        $httpGateway = new SatHttpGateway($client);
        $manager->setHttpGateway($httpGateway);

        $this->expectException(CiecLoginException::class);
        $this->expectExceptionMessage('It was unable to find the captcha image');
        $manager->requestCaptchaImage();
    }

    public function testRequestCaptchaImageWithConnectionError(): void
    {
        $rfc = 'COSC8001137NA';
        $ciec = 'CiecPassword';
        $resolver = $this->createMock(CaptchaResolverInterface::class);
        $manager = CiecSessionManager::create($rfc, $ciec, $resolver);

        $preparedResponses = new MockHandler([
            $this->createMock(SatHttpGatewayException::class),
        ]);
        $client = new GuzzleClient(['handler' => HandlerStack::create($preparedResponses)]);

        $httpGateway = new SatHttpGateway($client);
        $manager->setHttpGateway($httpGateway);

        $this->expectException(CiecLoginException::class);
        $this->expectExceptionMessage('Connection error when getting captcha image');
        $manager->requestCaptchaImage();
    }

    public function testRequestCaptchaImage(): void
    {
        $rfc = 'COSC8001137NA';
        $ciec = 'CiecPassword';
        $resolver = $this->createMock(CaptchaResolverInterface::class);
        $manager = CiecSessionManager::create($rfc, $ciec, $resolver);

        $preparedResponses = new MockHandler([
            // get regular login page
            new Response(200, ['Content-type' => 'text/html'], $this->fileContentPath('sample-response-ciec-login-form.html')),
        ]);
        $client = new GuzzleClient(['handler' => HandlerStack::create($preparedResponses)]);

        $httpGateway = new SatHttpGateway($client);
        $manager->setHttpGateway($httpGateway);

        $expectedCaptchaImage = CaptchaImage::newFromFile($this->filePath('sample-captcha.png'));
        $this->assertSame($expectedCaptchaImage->asBase64(), $manager->requestCaptchaImage()->asBase64());
    }
}
