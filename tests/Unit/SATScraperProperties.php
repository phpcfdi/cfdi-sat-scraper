<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PhpCfdi\CfdiSatScraper\URLS;

final class SATScraperProperties extends TestCase
{
    private function createScraper(): SATScraper
    {
        return new SATScraper(
            'rfc',
            'ciec',
            $this->createGuzzleClient(),
            $this->createCookieJar(),
            $this->createCaptchaResolver()
        );
    }

    private function createGuzzleClient(): Client
    {
        return new Client();
    }

    private function createCaptchaResolver(): CaptchaResolverInterface
    {
        /** @var CaptchaResolverInterface $captcha */
        $captcha = $this->createMock(CaptchaResolverInterface::class);
        return $captcha;
    }

    private function createCookieJar(): CookieJar
    {
        return new CookieJar();
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
        $callable = function () {
        };
        $this->assertSame($callable, $scraper->setOnFiveHundred($callable)->getOnFiveHundred());
        $this->assertNull($scraper->setOnFiveHundred(null)->getOnFiveHundred());
    }

    public function testClient(): void
    {
        $scraper = $this->createScraper();
        $client = $this->createGuzzleClient();
        $this->assertSame($client, $scraper->setClient($client)->getClient());
    }

    public function testCookie(): void
    {
        $scraper = $this->createScraper();
        $cookie = $this->createCookieJar();
        $this->assertSame($cookie, $scraper->setCookie($cookie)->getCookie());
    }

    public function testMaxTriesCaptcha(): void
    {
        $scraper = $this->createScraper();
        $this->assertSame(3, $scraper->getMaxTriesCaptcha(), 'Default MaxTriesCaptcha did not match');
        $tries = 6;
        $this->assertSame($tries, $scraper->setMaxTriesCaptcha($tries)->getMaxTriesCaptcha());
    }
}
