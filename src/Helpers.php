<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

class Helpers
{
    /**
     * @param int $seconds
     *
     * @return string
     */
    public static function converterSecondsToHours(int $seconds): string
    {
        return gmdate('H:i:s', $seconds);
    }

    /**
     * @param int $num
     *
     * @return string
     */
    public static function formatNumber(int $num): string
    {
        if ($num < 10) {
            $num = '0' . $num;
        }

        return (string)$num;
    }
}
