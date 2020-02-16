<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PhpCfdi\CfdiSatScraper\URLS;

final class SatScraperPropertiesTest extends TestCase
{
    private function createScraper(): SatScraper
    {
        return new SatScraper(
            'rfc',
            'ciec',
            $this->createCaptchaResolver(),
            $this->createSatHttpGateway()
        );
    }

    private function createSatHttpGateway(): SatHttpGateway
    {
        return new SatHttpGateway();
    }

    private function createCaptchaResolver(): CaptchaResolverInterface
    {
        /** @var CaptchaResolverInterface $captcha */
        $captcha = $this->createMock(CaptchaResolverInterface::class);
        return $captcha;
    }

    public function testRfc(): void
    {
        $this->assertSame('rfc', $this->createScraper()->getRfc());
    }

    public function testLoginUrl(): void
    {
        $scraper = $this->createScraper();
        $this->assertSame(URLS::SAT_URL_LOGIN, $scraper->getLoginUrl(), 'Default loginUrl not found');
        $newUrl = 'https://foo/bar';
        $this->assertSame($scraper, $scraper->setLoginUrl($newUrl), 'Expected fluent method');
        $this->assertSame($newUrl, $scraper->getLoginUrl());
    }

    public function testLoginUrlWithInvalidUrl(): void
    {
        $scraper = $this->createScraper();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The provided url is invalid');
        $scraper->setLoginUrl('');
    }

    public function testCaptchaResolver(): void
    {
        $captcha = $this->createCaptchaResolver();
        $scraper = $this->createScraper();
        $this->assertSame($scraper, $scraper->setCaptchaResolver($captcha), 'Expected fluent method');
        $this->assertSame($captcha, $scraper->getCaptchaResolver());
    }

    public function testOnFiveHundred(): void
    {
        $scraper = $this->createScraper();
        $this->assertNull($scraper->getOnFiveHundred(), 'Default OnFiveHundred should be NULL');
        $callable = function (): void {
        };
        $this->assertSame($callable, $scraper->setOnFiveHundred($callable)->getOnFiveHundred());
        $this->assertNull($scraper->setOnFiveHundred(null)->getOnFiveHundred());
    }

    public function testSatHttpGateway(): void
    {
        $scraper = $this->createScraper();
        $satHttpGateway = $this->createSatHttpGateway();
        $this->assertSame($satHttpGateway, $scraper->setSatHttpGateway($satHttpGateway)->getSatHttpGateway());
    }

    public function testMaxTriesCaptcha(): void
    {
        $scraper = $this->createScraper();
        $this->assertSame(3, $scraper->getMaxTriesCaptcha(), 'Default MaxTriesCaptcha did not match');
        $tries = 6;
        $this->assertSame($tries, $scraper->setMaxTriesCaptcha($tries)->getMaxTriesCaptcha());
    }

    public function testMaxTriesLogin(): void
    {
        $scraper = $this->createScraper();
        $this->assertSame(3, $scraper->getMaxTriesLogin(), 'Default MaxTriesCaptcha did not match');
        $tries = 6;
        $this->assertSame($tries, $scraper->setMaxTriesLogin($tries)->getMaxTriesLogin());
    }
}
