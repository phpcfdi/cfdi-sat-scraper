<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Sessions\Ciec;

use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionData;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;

final class CiecSessionDataTest extends TestCase
{
    private function createDefaultSessionData(): CiecSessionData
    {
        return new CiecSessionData('rfc', 'ciec', $this->createCaptchaResolver());
    }

    private function createCaptchaResolver(): CaptchaResolverInterface
    {
        return $this->createMock(CaptchaResolverInterface::class);
    }

    public function testConstructWithEmptyRfc(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RFC');
        new CiecSessionData('', 'ciec', $this->createCaptchaResolver());
    }

    public function testConstructWithEmptyCiec(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CIEC');
        new CiecSessionData('rfc', '', $this->createCaptchaResolver());
    }

    public function testRfc(): void
    {
        $this->assertSame('rfc', $this->createDefaultSessionData()->getRfc());
    }

    public function testCiec(): void
    {
        $this->assertSame('ciec', $this->createDefaultSessionData()->getCiec());
    }

    public function testCaptchaResolver(): void
    {
        $captcha = $this->createCaptchaResolver();
        $data = new CiecSessionData('rfc', 'ciec', $captcha);
        $this->assertSame($captcha, $data->getCaptchaResolver());
    }

    public function testMaxTriesCaptcha(): void
    {
        $tries = 6;
        $data = new CiecSessionData('rfc', 'ciec', $this->createCaptchaResolver(), $tries);
        $this->assertSame($tries, $data->getMaxTriesCaptcha(), 'Given MaxTriesCaptcha did not match');
    }

    public function testMaxTriesCaptchaDefault(): void
    {
        $data = $this->createDefaultSessionData();
        $this->assertSame(CiecSessionData::DEFAULT_MAX_TRIES_CAPTCHA, $data->getMaxTriesCaptcha(), 'Default MaxTriesCaptcha did not match');
    }

    public function testMaxTriesLogin(): void
    {
        $tries = 6;
        $data = new CiecSessionData('rfc', 'ciec', $this->createCaptchaResolver(), 0, $tries);
        $this->assertSame($tries, $data->getMaxTriesLogin(), 'Given MaxTriesLogin did not match');
    }

    public function testMaxTriesLoginDefault(): void
    {
        $data = $this->createDefaultSessionData();
        $this->assertSame(CiecSessionData::DEFAULT_MAX_TRIES_LOGIN, $data->getMaxTriesLogin(), 'Default MaxTriesLogin did not match');
    }
}
