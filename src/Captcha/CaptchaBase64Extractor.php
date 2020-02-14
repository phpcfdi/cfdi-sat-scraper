<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Captcha;

use Symfony\Component\DomCrawler\Crawler;

class CaptchaBase64Extractor
{
    public const DEFAULT_SELECTOR = '#divCaptcha > img';

    /**
     * @param string $htmlSource
     * @param string $selector
     * @return string
     */
    public function retrieve(string $htmlSource, string $selector = ''): string
    {
        $selector = $selector ?: self::DEFAULT_SELECTOR;

        $images = (new Crawler($htmlSource))->filter($selector);
        if (0 === $images->count()) {
            throw new \RuntimeException('Captcha was not found');
        }
        $firstImage = $images->first();

        $srcContent = strval($firstImage->attr('src'));
        $srcContent = str_replace('data:image/jpeg;base64,', '', $srcContent);

        return $srcContent;
    }
}
