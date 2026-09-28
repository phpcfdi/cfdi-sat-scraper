<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use Generator;
use PhpCfdi\CfdiSatScraper\QueryByFilters;

final readonly class MetadataPackageQuerySplitter
{
    public function __construct(private QueryByFilters $query)
    {
    }

    /**
     * Generates a clone of the query split by day or whole month when the download type is "recibidos";
     * otherwise the query itself.
     *
     * @return Generator<QueryByFilters>
     */
    public function split(): Generator
    {
        $startDate = $this->query->getStartDate();
        $endDate = $this->query->getEndDate();

        if ($this->query->getDownloadType()->isEmitidos()) {
            yield $this->query;
            return;
        }

        if ($startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
            yield $this->query;
            return;
        }

        $date = $startDate;
        while ($date <= $endDate) {
            if (
                // if the entire month can be used
                '01 00:00:00' === $date->format('d H:i:s')
                && $date->modify('last day of this month 23:59:59') <= $endDate
            ) {
                $limit = $date->modify('last day of this month 23:59:59');
            } else {
                $limit = min($endDate, $date->setTime(23, 59, 59));
            }
            yield (clone $this->query)->setPeriod($date, $limit);
            $date = $limit->modify('midnight +1 day');
        }
    }
}
