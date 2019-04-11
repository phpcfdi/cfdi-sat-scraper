<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters;

use PhpCfdi\CfdiSatScraper\Contracts\Filters;

/**
 * Class FiltersReceived.
 */
class FiltersReceived extends BaseFilters implements Filters
{
    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            '__ASYNCPOST' => 'true',
            '__EVENTARGUMENT' => '',
            '__EVENTTARGET' => '',
            '__LASTFOCUS' => '',
            'ctl00$MainContent$BtnBusqueda' => 'Buscar CFDI',
            'ctl00$MainContent$CldFecha$DdlAnio' => $this->query->getStartDate()->format('Y'),
            'ctl00$MainContent$CldFecha$DdlMes' => $this->query->getStartDate()->format('n'),
            'ctl00$MainContent$CldFecha$DdlDia' => $this->query->getStartDate()->format('d'),
            'ctl00$MainContent$CldFecha$DdlHora' => $this->query->getStartDate()->format('H'),
            'ctl00$MainContent$CldFecha$DdlMinuto' => $this->query->getStartDate()->format('i'),
            'ctl00$MainContent$CldFecha$DdlSegundo' => $this->query->getStartDate()->format('s'),
            'ctl00$MainContent$CldFecha$DdlHoraFin' => $this->query->getEndDate()->format('H'),
            'ctl00$MainContent$CldFecha$DdlMinutoFin' => $this->query->getEndDate()->format('i'),
            'ctl00$MainContent$CldFecha$DdlSegundoFin' => $this->query->getEndDate()->format('s'),
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
            'ctl00$MainContent$CldFecha$DdlAnio' => date('Y'),
            'ctl00$MainContent$CldFecha$DdlDia' => '0',
            'ctl00$MainContent$CldFecha$DdlHora' => '0',
            'ctl00$MainContent$CldFecha$DdlHoraFin' => '23',
            'ctl00$MainContent$CldFecha$DdlMes' => '1',
            'ctl00$MainContent$CldFecha$DdlMinuto' => '0',
            'ctl00$MainContent$CldFecha$DdlMinutoFin' => '59',
            'ctl00$MainContent$CldFecha$DdlSegundo' => '0',
            'ctl00$MainContent$CldFecha$DdlSegundoFin' => '59',
            'ctl00$MainContent$DdlEstadoComprobante' => '-1',
            'ctl00$MainContent$FiltroCentral' => 'RdoFechas',
            'ctl00$MainContent$TxtRfcReceptor' => '',
            'ctl00$MainContent$TxtUUID' => '',
            'ctl00$MainContent$ddlComplementos' => '-1',
            'ctl00$MainContent$hfInicialBool' => 'true',
            'ctl00$ScriptManager1' => 'ctl00$MainContent$UpnlBusqueda|ctl00$MainContent$RdoFechas',
        ];
    }
}
