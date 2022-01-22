<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Inputs;

use PhpCfdi\CfdiSatScraper\QueryByFilters;

class InputsByFiltersReceived extends InputsByFilters implements InputsInterface
{
    /** @return array<string, string> */
    public function getDateFilters(): array
    {
        /** @var QueryByFilters $query PhpStorm does not know correct type by template */
        $query = $this->getQuery();
        $startDate = $query->getStartDate();
        $endDate = $query->getEndDate();
        return [
            'ctl00$MainContent$CldFecha$DdlAnio' => $startDate->format('Y'),
            'ctl00$MainContent$CldFecha$DdlMes' => $this->sidate($startDate, 'm', 1),
            'ctl00$MainContent$CldFecha$DdlDia' => $this->sidate($startDate, 'd', 2),
            'ctl00$MainContent$CldFecha$DdlHora' => $this->sidate($startDate, 'H', 1),
            'ctl00$MainContent$CldFecha$DdlMinuto' => $this->sidate($startDate, 'i', 1),
            'ctl00$MainContent$CldFecha$DdlSegundo' => $this->sidate($startDate, 's', 1),
            'ctl00$MainContent$CldFecha$DdlHoraFin' => $this->sidate($endDate, 'H', 1),
            'ctl00$MainContent$CldFecha$DdlMinutoFin' => $this->sidate($endDate, 'i', 1),
            'ctl00$MainContent$CldFecha$DdlSegundoFin' => $this->sidate($endDate, 's', 1),
        ];
    }
}
