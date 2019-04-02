<?php

namespace PhpCfdi\CfdiSatScraper;

class Headers
{
    /**
     * @param string $host
     * @param string $referer
     *
     * @return array
     */
    public static function post($host, $referer)
    {
        return [
            'Accept' => ' text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Connection' => 'keep-alive',
            'Host' => $host,
            'Referer' => $referer,
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
    }

    /**
     * @param string $host
     * @param string $referer
     *
     * @return array
     */
    public static function postAjax($host, $referer)
    {
        return [
            'Accept' => ' text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'Host' => $host,
            'Referer' => $referer,
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'X-MicrosoftAjax' => 'Delta=true',
            'x-requested-with' => 'XMLHttpRequest',
            'Pragma' => 'no-cache',
        ];
    }
}
