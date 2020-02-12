<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters;

use PhpCfdi\CfdiSatScraper\Contracts\Filters;

/**
 * Class FiltersIssued.
 */
class FiltersIssued extends BaseFilters implements Filters
{
    public function getFilters(): array
    {
        return array_merge($this->getInitialFilters(), [
            'ctl00$MainContent$BtnBusqueda' => 'Buscar CFDI', // this is set to know that the event has been raised
            'ctl00$MainContent$hfInicial' => $this->query->getStartDate()->format('Y'),
            'ctl00$MainContent$CldFechaInicial2$Calendario_text' => $this->query->getStartDate()->format('d/m/Y'),
            'ctl00$MainContent$CldFechaInicial2$DdlHora' => $this->sidate($this->query->getStartDate(), 'H', 1),
            'ctl00$MainContent$CldFechaInicial2$DdlMinuto' => $this->sidate($this->query->getStartDate(), 'i', 1),
            'ctl00$MainContent$CldFechaInicial2$DdlSegundo' => $this->sidate($this->query->getStartDate(), 's', 1),
            'ctl00$MainContent$CldFechaFinal2$Calendario_text' => $this->query->getEndDate()->format('d/m/Y'),
            'ctl00$MainContent$hfFinal' => $this->query->getEndDate()->format('Y'),
            'ctl00$MainContent$CldFechaFinal2$DdlHora' => $this->sidate($this->query->getEndDate(), 'H', 1),
            'ctl00$MainContent$CldFechaFinal2$DdlMinuto' => $this->sidate($this->query->getEndDate(), 'i', 1),
            'ctl00$MainContent$CldFechaFinal2$DdlSegundo' => $this->sidate($this->query->getEndDate(), 's', 1),
        ]);
    }
}
