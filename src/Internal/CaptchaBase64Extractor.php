<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use PhpCfdi\CfdiSatScraper\Exceptions\RuntimeException;
use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use Symfony\Component\DomCrawler\Crawler;

/**
 * This is a class to extract the captcha from the log in web page.
 *
 * @internal
 */
class CaptchaBase64Extractor
{
    public const DEFAULT_SELECTOR = '#divCaptcha > img';

    public function retrieveCaptchaImage(string $htmlSource, string $selector = self::DEFAULT_SELECTOR): CaptchaImage
    {
        $images = (new Crawler($htmlSource))->filter($selector);

        if (0 === $images->count()) {
            throw RuntimeException::unableToFindCaptchaImage($selector);
        }

        $imageSource = (string) $images->attr('src');

        return CaptchaImage::newFromInlineHtml($imageSource);
    }
}
