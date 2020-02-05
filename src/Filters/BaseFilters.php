<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters;

use PhpCfdi\CfdiSatScraper\Contracts\Filters;
use PhpCfdi\CfdiSatScraper\Filters\Options\UuidOption;
use PhpCfdi\CfdiSatScraper\Query;

/**
 * Class BaseFilters.
 */
abstract class BaseFilters implements Filters
{
    /**
     * @var Query
     */
    protected $query;

    /**
     * BaseFilters constructor.
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * @return array
     */
    public function overrideDefaultFilters(): array
    {
        if ($this->query->hasUuids()) {
            $filters = [
                $this->query->getDownloadType(),
                new UuidOption($this->query->getUuid()[0] ?? ''),
            ];
        } else {
            $filters = [
                $this->query->getDownloadType(),
                $this->query->getComplement(),
                $this->query->getStateVoucher(),
                $this->query->getRfc(),
            ];
        }
        $filters = array_filter($filters);

        $overrideFilters = [];
        foreach ($filters as $filter) {
            $overrideFilters[$filter->nameIndex()] = $filter->value();
        }

        return $overrideFilters;
    }

    public function getRequestFilters(): array
    {
        $requestFilters = array_merge($this->getFilters(), $this->overrideDefaultFilters());

        return $requestFilters;
    }

    /**
     * Retrieve the CentralFilter data, if this query is about UUID then it is RdoFolioFiscal, else is RdoFechas
     *
     * @return string
     */
    protected function getCentralFilter(): string
    {
        if ($this->query->hasUuids()) {
            return 'RdoFolioFiscal';
        }

        return 'RdoFechas';
    }

    /**
     * Helper function that emulates idate($ts, $format) but using a DateTimeImmutable and padding leading zeros
     *
     * @param \DateTimeImmutable $date
     * @param string $format some value of date, use only those values that return an integer expression
     * @param int $fixedPositions expected minimal positions, will pad leading zeros if length is lower than fixed
     * @return string
     */
    protected function sidate(\DateTimeImmutable $date, string $format, int $fixedPositions = 1): string
    {
        $fixedPositions = max(1, $fixedPositions);
        return sprintf("%0{$fixedPositions}d", (int) $date->format($format));
    }
}
