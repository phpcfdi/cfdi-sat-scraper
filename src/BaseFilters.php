<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

/**
 * Class BaseFilters.
 */
abstract class BaseFilters
{
    public $year;

    public $month;

    public $day;

    public $taxId;

    public $hour_start;

    public $minute_start;

    public $second_start;

    public $hour_end;

    public $minute_end;

    public $second_end;

    public $stateVoucher;

    /**
     * BaseFilters constructor.
     */
    public function __construct()
    {
        $this->year = '2015';
        $this->month = '1';
        $this->day = '0';
        $this->taxId = '';
        $this->hour_start = '00';
        $this->minute_start = '00';
        $this->second_start = '00';
        $this->hour_end = '23';
        $this->minute_end = '59';
        $this->second_end = '59';
        $this->stateVoucher = '1';
    }

    /**
     * @return string
     */
    protected function dayFormat()
    {
        if ((int)$this->day < 10) {
            $this->day = '0' . (int)$this->day;
        }

        return (string)$this->day;
    }

    /**
     * @return string
     */
    protected function getCentralFilter()
    {
        if ('' != $this->taxId) {
            return 'RdoFolioFiscal';
        }

        return 'RdoFechas';
    }

    /**
     * @param int $pSecStart
     *
     * @return string
     */
    public function converterSecondsToHours($pSecStart)
    {
        $segStart = $pSecStart - 1;
        $hours = (int)floor($segStart / 3600);
        $minutes = (int)floor(($segStart - ($hours * 3600)) / 60);
        $seconds = (int)$segStart - ($hours * 3600) - ($minutes * 60);

        return $this->formatNumber($hours) . ':' . $this->formatNumber($minutes) . ':' . $this->formatNumber($seconds);
    }

    /**
     * @param int $hour
     *
     * @return string
     */
    public function converterHoursToSeconds($hour)
    {
        [$hours, $minutes, $seconds] = explode(':', $hour);
        $hour_in_seconds = ($hours * 3600) + ($minutes * 60) + $seconds + 1;

        return $hour_in_seconds;
    }

    /**
     * @param int $num
     *
     * @return string
     */
    protected function formatNumber($num)
    {
        if ((int)$num < 10) {
            $num = '0' . $num;
        }

        return (string)$num;
    }

    /**
     * @param $num
     *
     * @return int
     */
    protected function formatNumberInt($num)
    {
        return (int)$num;
    }
}
