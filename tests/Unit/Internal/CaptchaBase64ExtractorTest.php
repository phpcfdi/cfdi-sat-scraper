<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Internal;

use PhpCfdi\CfdiSatScraper\Exceptions\RuntimeException;
use PhpCfdi\CfdiSatScraper\Internal\CaptchaBase64Extractor;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class CaptchaBase64ExtractorTest extends TestCase
{
    private function getBase64Image(): string
    {
        return base64_encode($this->fileContentPath('sample-captcha.png'));
    }

    public function testRetrieveWhenDefaultElementExists(): void
    {
        $base64Image = $this->getBase64Image();
        $html = <<< HTML
            <div id="divCaptcha">
                <img src="data:image/jpeg;base64,$base64Image">
            </div>
            HTML;

        $captchaExtractor = new CaptchaBase64Extractor();
        $this->assertSame($base64Image, $captchaExtractor->retrieveCaptchaImage($html)->asBase64());
    }

    public function testRetrieveWhenDefaultElementNotExists(): void
    {
        $base64Image = $this->getBase64Image();
        $html = <<< HTML
            <div>
                <img src="data:image/jpeg;base64,$base64Image">
            </div>
            HTML;

        $captchaExtractor = new CaptchaBase64Extractor();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to find image using filter '#divCaptcha > img'");
        $captchaExtractor->retrieveCaptchaImage($html)->asBase64();
    }

    public function testRetrieveBySelectorWhenElementExists(): void
    {
        $base64Image = $this->getBase64Image();
        $html = <<< HTML
            <div id="captcha">
                <img src="data:image/jpeg;base64,$base64Image">
            </div>
            HTML;

        $captchaExtractor = new CaptchaBase64Extractor();
        $this->assertSame($base64Image, $captchaExtractor->retrieveCaptchaImage($html, '#captcha > img')->asBase64());
    }
}
