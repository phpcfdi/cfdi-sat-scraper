<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

/**
 * Helper class to return the correct headers for different requests: post and ajax.
 *
 * @internal
 */
class Headers
{
    /**
     * Return the headers to use on general form submit
     *
     * @param string $referer
     *
     * @return array<string, string>
     */
    public static function get(string $referer = ''): array
    {
        return array_filter([
            'Accept' => ' text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Accept-Charset' => 'utf-8, iso-8859-15;q=0.5',
            'Connection' => 'keep-alive',
            'Referer' => $referer,
            'User-Agent' => 'Mozilla/5.0 (Linux x86_64; rv:91.0) Gecko/20100101 Firefox/91.0',
        ]);
    }

    /**
     * Return the headers to use on general form submit
     *
     * @param string $host
     * @param string $referer
     *
     * @return array<string, string>
     */
    public static function post(string $host, string $referer): array
    {
        return array_merge(self::get($referer), array_filter([
            'Pragma' => 'no-cache',
            'Host' => $host,
            'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
        ]));
    }

    /**
     * Return the headers to use on ajax requests
     *
     * @param string $host
     * @param string $referer
     *
     * @return array<string, string>
     */
    public static function postAjax(string $host, string $referer): array
    {
        return array_merge(self::post($host, $referer), [
            'X-MicrosoftAjax' => 'Delta=true',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
    }
}
