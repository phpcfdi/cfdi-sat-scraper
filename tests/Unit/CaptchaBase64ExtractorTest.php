<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Captcha\CaptchaBase64Extractor;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

class CaptchaBase64ExtractorTest extends TestCase
{
    public function testRetrieveWhenDefaultElementExists(): void
    {
        $html = '<div id="divCaptcha">';
        $html .= '<img src="data:image/jpeg;base64,test">';
        $html .= '</div>';

        $captchaExtractor = new CaptchaBase64Extractor($html);
        $this->assertEquals('test', $captchaExtractor->retrieve());
    }

    public function testRetrieveWhenDefaultElementNotExists(): void
    {
        $html = '<div>';
        $html .= '<img src="data:image/jpeg;base64,test">';
        $html .= '</div>';

        $captchaExtractor = new CaptchaBase64Extractor($html);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Captcha was not found');
        $captchaExtractor->retrieve();
    }

    public function testRetrieveBySelectorWhenElementExists(): void
    {
        $html = '<div id="captcha">';
        $html .= '<img src="data:image/jpeg;base64,test">';
        $html .= '</div>';

        $captchaExtractor = new CaptchaBase64Extractor($html);
        $this->assertEquals('test', $captchaExtractor->retrieve('#captcha > img'));
    }
}
