<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Sessions\Ciec;

use Exception;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayClientException;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecLoginException;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionData;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;

final class CiecLoginExceptionTest extends TestCase
{
    public function testNotRegisteredAfterLogin(): void
    {
        /** @var CiecSessionData $sessionData */
        $sessionData = $this->createMock(CiecSessionData::class);
        $contents = 'x-contents';

        $exception = CiecLoginException::notRegisteredAfterLogin($sessionData, $contents);

        $this->assertStringContainsString(
            'It was expected to have the session registered on portal home page with RFC',
            $exception->getMessage(),
        );
        $this->assertSame($sessionData, $exception->getSessionData());
        $this->assertSame($contents, $exception->getContents());
    }

    public function testNoCaptchaImageFound(): void
    {
        /** @var CiecSessionData $sessionData */
        $sessionData = $this->createMock(CiecSessionData::class);
        $contents = 'x-contents';
        $previous = new Exception('previous');

        $exception = CiecLoginException::noCaptchaImageFound($sessionData, $contents, $previous);

        $this->assertStringContainsString('It was unable to find the captcha image', $exception->getMessage());
        $this->assertSame($sessionData, $exception->getSessionData());
        $this->assertSame($contents, $exception->getContents());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCaptchaWithoutAnswer(): void
    {
        /** @var CiecSessionData $sessionData */
        $sessionData = $this->createMock(CiecSessionData::class);
        /** @var CaptchaImageInterface $captchaImage */
        $captchaImage = $this->createMock(CaptchaImageInterface::class);
        $previous = new Exception('previous');

        $exception = CiecLoginException::captchaWithoutAnswer($sessionData, $captchaImage, $previous);

        $this->assertStringContainsString('Unable to decode captcha', $exception->getMessage());
        $this->assertSame($sessionData, $exception->getSessionData());
        $this->assertSame($captchaImage, $exception->getCaptchaImage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIncorrectLoginData(): void
    {
        /** @var CiecSessionData $sessionData */
        $sessionData = $this->createMock(CiecSessionData::class);
        $contents = 'x-contents';
        $postedData = [];

        $exception = CiecLoginException::incorrectLoginData($sessionData, $contents, $postedData);

        $this->assertStringContainsString('Incorrect login data', $exception->getMessage());
        $this->assertSame($sessionData, $exception->getSessionData());
        $this->assertSame($contents, $exception->getContents());
        $this->assertSame($postedData, $exception->getPostedData());
    }

    public function testConnectionException(): void
    {
        $when = 'x-when';
        /** @var CiecSessionData $sessionData */
        $sessionData = $this->createMock(CiecSessionData::class);
        /** @var SatHttpGatewayClientException $previous */
        $previous = $this->createMock(SatHttpGatewayClientException::class);

        $exception = CiecLoginException::connectionException($when, $sessionData, $previous);

        $this->assertStringContainsString("Connection error when $when", $exception->getMessage());
        $this->assertSame($sessionData, $exception->getSessionData());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
