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
}
