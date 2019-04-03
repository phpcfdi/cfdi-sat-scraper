<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

interface Filters
{
    /**
     * @return mixed
     */
    public function getPost();

    /**
     * @return mixed
     */
    public function getFormPostDates();

    /**
     * @param int $hour
     *
     * @return mixed
     */
    public function converterHoursToSeconds($hour);

    /**
     * @param int $pSecStart
     *
     * @return mixed
     */
    public function converterSecondsToHours($pSecStart);
}
