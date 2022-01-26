<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Inputs;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\QueryByFilters;

/**
 * @extends InputsGeneric<QueryByFilters>
 */
abstract class InputsByFilters extends InputsGeneric implements InputsInterface
{
    /** @return array<string, string> */
    abstract public function getDateFilters(): array;

    public function __construct(QueryByFilters $query)
    {
        parent::__construct($query);
    }

    public function getCentralFilter(): string
    {
        return 'RdoFechas';
    }

    public function getQueryAsInputs(): array
    {
        return array_merge(
            parent::getQueryAsInputs(),
            $this->getDateFilters(),
        );
    }

    public function getFilterOptions(): array
    {
        /** @var QueryByFilters $query */
        $query = $this->getQuery();
        return [$query->getComplement(), $query->getStateVoucher(), $query->getRfc()];
    }

    /**
     * Helper function that emulates idate($ts, $format) but using a DateTimeImmutable and padding leading zeros
     *
     * @param DateTimeImmutable $date
     * @param string $format some value of date, use only those values that return an integer expression
     * @param int $fixedPositions expected minimal positions, will pad leading zeros if length is lower than fixed
     * @return string
     */
    protected function sidate(DateTimeImmutable $date, string $format, int $fixedPositions = 1): string
    {
        $fixedPositions = max(1, $fixedPositions);
        return sprintf("%0{$fixedPositions}d", (int) $date->format($format));
    }
}
