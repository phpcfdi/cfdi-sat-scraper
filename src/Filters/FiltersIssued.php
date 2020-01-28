<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters;

use PhpCfdi\CfdiSatScraper\Contracts\Filters;

/**
 * Class FiltersIssued.
 */
class FiltersIssued extends BaseFilters implements Filters
{
    /**
     * @return array
     */
    public function getFilters(): array
    {
        $startYear = $this->query->getStartDate()->format('Y');
        $startMonth = $this->query->getStartDate()->format('n');
        $startDay = $this->query->getStartDate()->format('d');

        return [
            '__ASYNCPOST' => 'true',
            '__EVENTARGUMENT' => '',
            '__EVENTTARGET' => '',
            '__LASTFOCUS' => '',
            'ctl00$MainContent$BtnBusqueda' => 'Buscar CFDI',
            'ctl00$MainContent$CldFechaInicial2$Calendario_text' => $startDay . '/' . $startMonth . '/' . $startYear,
            'ctl00$MainContent$CldFechaInicial2$DdlHora' => $this->query->getStartDate()->format('H'),
            'ctl00$MainContent$CldFechaInicial2$DdlMinuto' => $this->query->getStartDate()->format('i'),
            'ctl00$MainContent$CldFechaInicial2$DdlSegundo' => $this->query->getStartDate()->format('s'),
            'ctl00$MainContent$CldFechaFinal2$Calendario_text' => $startDay . '/' . $startMonth . '/' . $startYear,
            'ctl00$MainContent$CldFechaFinal2$DdlHora' => $this->query->getEndDate()->format('H'),
            'ctl00$MainContent$CldFechaFinal2$DdlMinuto' => $this->query->getEndDate()->format('i'),
            'ctl00$MainContent$CldFechaFinal2$DdlSegundo' => $this->query->getEndDate()->format('s'),
            'ctl00$MainContent$DdlEstadoComprobante' => '1',
            'ctl00$MainContent$FiltroCentral' => $this->getCentralFilter(),
            'ctl00$MainContent$TxtRfcReceptor' => '',
            'ctl00$MainContent$TxtUUID' => '',
            'ctl00$MainContent$ddlComplementos' => '-1',
            'ctl00$MainContent$hfInicialBool' => 'false',
            'ctl00$ScriptManager1' => 'ctl00$MainContent$UpnlBusqueda|ctl00$MainContent$BtnBusqueda',
        ];
    }

    /**
     * @return array
     */
    public function getInitialFilters(): array
    {
        return [
            '__ASYNCPOST' => 'true',
            '__EVENTARGUMENT' => '',
            '__EVENTTARGET' => 'ctl00$MainContent$RdoFechas',
            '__LASTFOCUS' => '',
            'ctl00$MainContent$CldFechaInicial2$Calendario_text' => '',
            'ctl00$MainContent$CldFechaInicial2$DdlHora' => '0',
            'ctl00$MainContent$CldFechaInicial2$DdlMinuto' => '0',
            'ctl00$MainContent$CldFechaInicial2$DdlSegundo' => '0',
            'ctl00$MainContent$CldFechaFinal2$Calendario_text' => '',
            'ctl00$MainContent$CldFechaFinal2$DdlHora' => '0',
            'ctl00$MainContent$CldFechaFinal2$DdlMinuto' => '0',
            'ctl00$MainContent$CldFechaFinal2$DdlSegundo' => '0',
            'ctl00$MainContent$DdlEstadoComprobante' => '-1',
            'ctl00$MainContent$ddlComplementos' => '-1',
            'ctl00$MainContent$FiltroCentral' => 'RdoFechas',
            'ctl00$MainContent$TxtRfcReceptor' => '',
            'ctl00$MainContent$TxtUUID' => '',
            'ctl00$MainContent$hfInicialBool' => 'true',
            'ctl00$ScriptManager1' => 'ctl00$MainContent$UpnlBusqueda|ctl00$MainContent$RdoFechas',
        ];
    }
}
