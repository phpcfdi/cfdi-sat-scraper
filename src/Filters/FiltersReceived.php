<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters;

use PhpCfdi\CfdiSatScraper\Contracts\Filters;

/**
 * Class FiltersReceived.
 */
class FiltersReceived extends BaseFilters implements Filters
{
    public function getFilters(): array
    {
        return array_merge($this->getInitialFilters(), [
            'ctl00$MainContent$BtnBusqueda' => 'Buscar CFDI', // this is set to know that the event has been raised
            'ctl00$MainContent$CldFecha$DdlAnio' => $this->query->getStartDate()->format('Y'),
            'ctl00$MainContent$CldFecha$DdlMes' => $this->sidate($this->query->getStartDate(), 'm', 1),
            'ctl00$MainContent$CldFecha$DdlDia' => $this->sidate($this->query->getStartDate(), 'd', 2),
            'ctl00$MainContent$CldFecha$DdlHora' => $this->sidate($this->query->getStartDate(), 'H', 1),
            'ctl00$MainContent$CldFecha$DdlMinuto' => $this->sidate($this->query->getStartDate(), 'i', 1),
            'ctl00$MainContent$CldFecha$DdlSegundo' => $this->sidate($this->query->getStartDate(), 's', 1),
            'ctl00$MainContent$CldFecha$DdlHoraFin' => $this->sidate($this->query->getEndDate(), 'H', 1),
            'ctl00$MainContent$CldFecha$DdlMinutoFin' => $this->sidate($this->query->getEndDate(), 'i', 1),
            'ctl00$MainContent$CldFecha$DdlSegundoFin' => $this->sidate($this->query->getEndDate(), 's', 1),
        ]);
    }
}
