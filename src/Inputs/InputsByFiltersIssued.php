<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Inputs;

use PhpCfdi\CfdiSatScraper\QueryByFilters;

class InputsByFiltersIssued extends InputsByFilters implements InputsInterface
{
    /** @return array<string, string> */
    public function getDateFilters(): array
    {
        /** @var QueryByFilters $query PhpStorm does not know correct type by template */
        $query = $this->getQuery();
        $startDate = $query->getStartDate();
        $endDate = $query->getEndDate();
        return [
            'ctl00$MainContent$hfInicial' => $startDate->format('Y'),
            'ctl00$MainContent$CldFechaInicial2$Calendario_text' => $startDate->format('d/m/Y'),
            'ctl00$MainContent$CldFechaInicial2$DdlHora' => $this->sidate($startDate, 'H', 1),
            'ctl00$MainContent$CldFechaInicial2$DdlMinuto' => $this->sidate($startDate, 'i', 1),
            'ctl00$MainContent$CldFechaInicial2$DdlSegundo' => $this->sidate($startDate, 's', 1),
            'ctl00$MainContent$CldFechaFinal2$Calendario_text' => $endDate->format('d/m/Y'),
            'ctl00$MainContent$hfFinal' => $endDate->format('Y'),
            'ctl00$MainContent$CldFechaFinal2$DdlHora' => $this->sidate($endDate, 'H', 1),
            'ctl00$MainContent$CldFechaFinal2$DdlMinuto' => $this->sidate($endDate, 'i', 1),
            'ctl00$MainContent$CldFechaFinal2$DdlSegundo' => $this->sidate($endDate, 's', 1),
        ];
    }
}
