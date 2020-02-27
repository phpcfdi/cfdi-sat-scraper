<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Captcha;

use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

class CaptchaBase64Extractor
{
    public const DEFAULT_SELECTOR = '#divCaptcha > img';

    public function retrieve(string $htmlSource, string $selector = ''): string
    {
        $selector = $selector ?: self::DEFAULT_SELECTOR;

        try {
            $images = (new Crawler($htmlSource))->filter($selector);
        } catch (RuntimeException $exception) {
            return '';
        }
        if (0 === $images->count()) {
            return '';
        }

        $firstImage = $images->first();
        $srcContent = strval($firstImage->attr('src'));
        $srcContent = str_replace('data:image/jpeg;base64,', '', $srcContent);

        return $srcContent;
    }
}
