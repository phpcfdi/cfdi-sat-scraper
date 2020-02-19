<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\SatSessionData;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class SatSessionDataTest extends TestCase
{
    private function createDefaultSessionData(): SatSessionData
    {
        return new SatSessionData('rfc', 'ciec', $this->createCaptchaResolver());
    }

    private function createCaptchaResolver(): CaptchaResolverInterface
    {
        /** @var CaptchaResolverInterface $captcha */
        $captcha = $this->createMock(CaptchaResolverInterface::class);
        return $captcha;
    }

    public function testConstructWithEmptyRfc(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RFC');
        new SatSessionData('', 'ciec', $this->createCaptchaResolver());
    }

    public function testConstructWithEmptyCiec(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CIEC');
        new SatSessionData('rfc', '', $this->createCaptchaResolver());
    }

    public function testRfc(): void
    {
        $this->assertSame('rfc', $this->createDefaultSessionData()->getRfc());
    }

    public function testCaptchaResolver(): void
    {
        $captcha = $this->createCaptchaResolver();
        $data = new SatSessionData('rfc', 'ciec', $captcha);
        $this->assertSame($captcha, $data->getCaptchaResolver());
    }

    public function testMaxTriesCaptcha(): void
    {
        $tries = 6;
        $data = new SatSessionData('rfc', 'ciec', $this->createCaptchaResolver(), $tries);
        $this->assertSame($tries, $data->getMaxTriesCaptcha(), 'Given MaxTriesCaptcha did not match');
    }

    public function testMaxTriesCaptchaDefault(): void
    {
        $data = $this->createDefaultSessionData();
        $this->assertSame(SatSessionData::DEFAULT_MAX_TRIES_CAPTCHA, $data->getMaxTriesCaptcha(), 'Default MaxTriesCaptcha did not match');
    }

    public function testMaxTriesLogin(): void
    {
        $tries = 6;
        $data = new SatSessionData('rfc', 'ciec', $this->createCaptchaResolver(), 0, $tries);
        $this->assertSame($tries, $data->getMaxTriesLogin(), 'Given MaxTriesLogin did not match');
    }

    public function testMaxTriesLoginDefault(): void
    {
        $data = $this->createDefaultSessionData();
        $this->assertSame(SatSessionData::DEFAULT_MAX_TRIES_LOGIN, $data->getMaxTriesLogin(), 'Default MaxTriesLogin did not match');
    }
}
