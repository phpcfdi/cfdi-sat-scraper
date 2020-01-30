<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters;

use PhpCfdi\CfdiSatScraper\Query;

/**
 * Class BaseFilters.
 */
abstract class BaseFilters
{
    /**
     * @var Query
     */
    protected $query;

    /**
     * @var
     */
    protected $uuid;

    /**
     * BaseFilters constructor.
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * @param $uuid
     *
     * @return BaseFilters
     */
    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return array
     */
    public function overrideDefaultFilters(): array
    {
        $overrideFilters = [];

        $filters = [
            $this->query->getDownloadType(),
            $this->query->getComplement(),
            $this->query->getStateVoucher(),
            $this->query->getRfc(),
            $this->query->getUuid(),
        ];

        foreach ($filters as $filter) {
            if (is_null($filter)) {
                continue;
            }

            $overrideFilters[$filter->nameIndex()] = $filter->value();
        }

        return $overrideFilters;
    }

    /**
     * @return array
     */
    public function getRequestFilters(): array
    {
        $requestFilters = array_merge($this->getFilters(), $this->overrideDefaultFilters());

        return $requestFilters;
    }

    /**
     * @return array
     */
    abstract public function getFilters(): array;

    /**
     * @return array
     */
    abstract public function getInitialFilters(): array;

    /**
     * @return string
     */
    protected function getCentralFilter()
    {
        if (! empty($this->uuid)) {
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
