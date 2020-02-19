<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\SatSessionData;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class SatScraperPropertiesTest extends TestCase
{
    private function createSatScraper(?SatSessionData $sessionData = null, ?SatHttpGateway $satHttpGateway = null, ?callable $onFiveHundred = null): SatScraper
    {
        return new SatScraper(
            $sessionData ?? new SatSessionData('rfc', 'ciec', $this->createCaptchaResolver()),
            $satHttpGateway,
            $onFiveHundred
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

    public function testSatSessionData(): void
    {
        $data = new SatSessionData('rfc', 'ciec', $this->createCaptchaResolver());
        $scraper = $this->createSatScraper($data);
        $this->assertSame($data, $scraper->getSatSessionData());
    }

    public function testSatHttpGateway(): void
    {
        $satHttpGateway = $this->createSatHttpGateway();
        $scraper = $this->createSatScraper(null, $satHttpGateway);
        $this->assertSame($satHttpGateway, $scraper->getSatHttpGateway());
    }

    public function testSatHttpGatewayDefault(): void
    {
        $scraper = $this->createSatScraper();
        $this->assertInstanceOf(SatHttpGateway::class, $scraper->getSatHttpGateway());
    }

    public function testOnFiveHundred(): void
    {
        $callable = function (): void {
        };
        $scraper = $this->createSatScraper(null, null, $callable);
        $this->assertSame($callable, $scraper->getOnFiveHundred(), 'Given OnFiveHundred was not the same');
    }

    public function testOnFiveHundredDefault(): void
    {
        $scraper = $this->createSatScraper();
        $this->assertNull($scraper->getOnFiveHundred(), 'Default OnFiveHundred should be NULL');
    }
}
