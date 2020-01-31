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
            'ctl00$MainContent$CldFecha$DdlMes' => $this->sidate($this->query->getStartDate(), 'm', 1),
            'ctl00$MainContent$CldFecha$DdlDia' => $this->sidate($this->query->getStartDate(), 'd', 2),
            'ctl00$MainContent$CldFecha$DdlHora' => $this->sidate($this->query->getStartDate(), 'H', 1),
            'ctl00$MainContent$CldFecha$DdlMinuto' => $this->sidate($this->query->getStartDate(), 'i', 1),
            'ctl00$MainContent$CldFecha$DdlSegundo' => $this->sidate($this->query->getStartDate(), 's', 1),
            'ctl00$MainContent$CldFecha$DdlHoraFin' => $this->sidate($this->query->getEndDate(), 'H', 1),
            'ctl00$MainContent$CldFecha$DdlMinutoFin' => $this->sidate($this->query->getEndDate(), 'i', 1),
            'ctl00$MainContent$CldFecha$DdlSegundoFin' => $this->sidate($this->query->getEndDate(), 's', 1),
            'ctl00$MainContent$DdlEstadoComprobante' => '1',
            'ctl00$MainContent$FiltroCentral' => $this->getCentralFilter(),
            'ctl00$MainContent$TxtRfcReceptor' => '',
            'ctl00$MainContent$TxtUUID' => '',
            'ctl00$MainContent$ddlComplementos' => '-1',
            'ctl00$MainContent$hfInicialBool' => 'false',
            'ctl00$MainContent$hfDescarga' => '',
            'ctl00$MainContent$ddlVigente' => '0',
            'ctl00$MainContent$ddlCancelado' => '0',
            'ctl00$MainContent$hfParametrosMetadata' => '',
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
            'ctl00$MainContent$hfDescarga' => '',
            'ctl00$MainContent$ddlVigente' => '0',
            'ctl00$MainContent$ddlCancelado' => '0',
            'ctl00$MainContent$hfParametrosMetadata' => '',
            'ctl00$ScriptManager1' => 'ctl00$MainContent$UpnlBusqueda|ctl00$MainContent$RdoFechas',
        ];
    }
}
