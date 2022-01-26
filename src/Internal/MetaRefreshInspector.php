<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\UriResolver;

/**
 * This is a class to inspect a web page and find if there is a http meta refresh instruction.
 *
 * @internal
 */
final class MetaRefreshInspector
{
    public function obtainUrl(string $html, string $baseUri): string
    {
        $crawler = new Crawler($html, $baseUri);

        $refresh = $crawler->filter('head meta[http-equiv="refresh"]');
        if (1 !== $refresh->count()) {
            return '';
        }

        $content = (string) $refresh->attr('content');
        if (! (bool) preg_match('/^\d+;\s*url=(?<url>.*)$/i', $content, $matches)) {
            return '';
        }

        $url = trim($matches['url'] ?? '');
        if ('' === $url) {
            return '';
        }

        $uriResolver = new UriResolver();
        if ('' !== $baseUri) {
            $url = $uriResolver->resolve($url, $baseUri);
        }

        return $url;
    }
}
