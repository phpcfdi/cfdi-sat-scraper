<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

class Helpers
{
    /**
     * @param int $pSecStart
     *
     * @return string
     */
    public static function converterSecondsToHours(int $pSecStart): string
    {
        $segStart = $pSecStart - 1;
        $hours = (int)floor($segStart / 3600);
        $minutes = (int)floor(($segStart - ($hours * 3600)) / 60);
        $seconds = (int)$segStart - ($hours * 3600) - ($minutes * 60);

        return self::formatNumber($hours) . ':' . self::formatNumber($minutes) . ':' . self::formatNumber($seconds);
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
