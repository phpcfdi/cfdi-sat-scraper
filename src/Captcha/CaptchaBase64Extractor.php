<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Captcha;

use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

class CaptchaBase64Extractor
{
    public const DEFAULT_SELECTOR = '#divCaptcha > img';

    public function retrieveCaptchaImage(string $htmlSource, string $selector = self::DEFAULT_SELECTOR): CaptchaImage
    {
        $images = (new Crawler($htmlSource))->filter($selector);

        if (0 === $images->count()) {
            throw new RuntimeException("Unable to find image using filter '$selector'");
        }

        $imageSource = (string) $images->attr('src');

        return CaptchaImage::newFromInlineHtml($imageSource);
    }
}
