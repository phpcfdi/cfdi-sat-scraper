<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Captcha;

use Symfony\Component\DomCrawler\Crawler;

class CaptchaBase64Extractor
{
    /** @var string */
    private $html;

    /**
     * CaptchaBase64Extractor constructor.
     * @param string $html
     */
    public function __construct(string $html)
    {
        $this->html = $html;
    }

    /**
     * @param string|null $selector
     * @return Crawler
     */
    private function findImg(?string $selector): Crawler
    {
        $selector = $selector ?? '#divCaptcha > img';

        $img = (new Crawler($this->html))
            ->filter($selector);

        if (0 === $img->count()) {
            throw new \RuntimeException('Captcha was not found');
        }

        return $img->first();
    }

    /**
     * @param string|null $selector
     * @return string
     */
    private function getSrc(?string $selector): string
    {
        $img = $this->findImg($selector);
        $src = $img->attr('src');

        return str_replace('data:image/jpeg;base64,', '', $src);
    }

    /**
     * @param string|null $selector
     * @return string
     */
    public function retrieve(?string $selector = null): string
    {
        return $this->getSrc($selector);
    }
}
