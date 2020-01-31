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
        return [
            '__ASYNCPOST' => 'true',
            '__EVENTARGUMENT' => '',
            '__EVENTTARGET' => '',
            '__LASTFOCUS' => '',
            'ctl00$MainContent$BtnBusqueda' => 'Buscar CFDI',
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
            'ctl00$MainContent$DdlEstadoComprobante' => '1',
            'ctl00$MainContent$FiltroCentral' => $this->getCentralFilter(),
            'ctl00$MainContent$TxtRfcReceptor' => '',
            'ctl00$MainContent$TxtUUID' => '',
            'ctl00$MainContent$ddlComplementos' => '-1',
            'ctl00$MainContent$hfInicialBool' => 'false',
            'ctl00$ScriptManager1' => 'ctl00$MainContent$UpnlBusqueda|ctl00$MainContent$BtnBusqueda',
            'ctl00$MainContent$ddlVigente' => '',
            'ctl00$MainContent$ddlCancelado' => '',
            'ctl00$MainContent$hfDatos' => '',
            'ctl00$MainContent$hfFlag' => '',
            'ctl00$MainContent$hfAux' => '',
            'ctl00$MainContent$hfDescarga' => '',
            'ctl00$MainContent$hfCancelacion' => '',
            'ctl00$MainContent$hfUrlDescarga' => '',
            'ctl00$MainContent$hfParametrosMetadata' => '',
            'ctl00$MainContent$hdnValAccion' => '',
            'ctl00$MainContent$hfXML' => '',
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
