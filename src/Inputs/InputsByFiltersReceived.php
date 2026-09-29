<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Inputs;

use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\QueryByFilters;

class InputsByFiltersReceived extends InputsByFilters implements InputsInterface
{
    public function __construct(QueryByFilters $query)
    {
        if (! $query->getDownloadType()->isRecibidos()) {
            throw new LogicException('Query is not for download type received');
        }

        // validate that query is just for one day or is for the whole month
        if (! $this->isWholeMonth($query) && ! $this->isForOnlyOneDay($query)) {
            throw new LogicException('Query is not for only one date or for the whole month');
        }

        parent::__construct($query);
    }

    /** @return array<string, string> */
    public function getDateFilters(): array
    {
        $query = $this->getQuery();
        $startDate = $query->getStartDate();
        $endDate = $query->getEndDate();
        $isWholeMonth = $this->isWholeMonth($query);

        return [
            'ctl00$MainContent$CldFecha$DdlAnio' => $startDate->format('Y'),
            'ctl00$MainContent$CldFecha$DdlMes' => $this->sidate($startDate, 'm', 1),
            'ctl00$MainContent$CldFecha$DdlDia' => $isWholeMonth ? '0' : $this->sidate($startDate, 'd', 2),
            'ctl00$MainContent$CldFecha$DdlHora' => $this->sidate($startDate, 'H', 1),
            'ctl00$MainContent$CldFecha$DdlMinuto' => $this->sidate($startDate, 'i', 1),
            'ctl00$MainContent$CldFecha$DdlSegundo' => $this->sidate($startDate, 's', 1),
            'ctl00$MainContent$CldFecha$DdlHoraFin' => $this->sidate($endDate, 'H', 1),
            'ctl00$MainContent$CldFecha$DdlMinutoFin' => $this->sidate($endDate, 'i', 1),
            'ctl00$MainContent$CldFecha$DdlSegundoFin' => $this->sidate($endDate, 's', 1),
        ];
    }

    /**
     * The query period covers a whole month when it starts on the first day at 00:00:00
     * and ends on the last day of the same month at 23:59:59.
     */
    private function isWholeMonth(QueryByFilters $query): bool
    {
        $startDate = $query->getStartDate();
        $endDate = $query->getEndDate();

        return $startDate->format('Y-m') === $endDate->format('Y-m')
            && '01' === $startDate->format('d')
            && $endDate->format('d') === $endDate->format('t')
            && '00:00:00' === $startDate->format('H:i:s')
            && '23:59:59' === $endDate->format('H:i:s');
    }

    private function isForOnlyOneDay(QueryByFilters $query): bool
    {
        return $query->getStartDate()->format('Y-m-d') === $query->getEndDate()->format('Y-m-d');
    }
}
