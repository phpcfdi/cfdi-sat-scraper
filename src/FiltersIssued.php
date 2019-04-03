<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Contracts\Filters;

/**
 * Class FiltersIssued.
 */
class FiltersIssued extends BaseFilters implements Filters
{
    /**
     * @return array
     */
    public function getPost()
    {
        $post = [];
        $post['__ASYNCPOST'] = 'true';
        $post['__EVENTARGUMENT'] = '';
        $post['__EVENTTARGET'] = '';
        $post['__LASTFOCUS'] = '';
        $post['ctl00$MainContent$BtnBusqueda'] = 'Buscar CFDI';
        $post['ctl00$MainContent$CldFechaInicial2$Calendario_text'] = $this->dayFormat() . '/' . $this->month . '/' . $this->year;
        $post['ctl00$MainContent$CldFechaInicial2$DdlHora'] = $this->formatNumberInt($this->hour_start);
        $post['ctl00$MainContent$CldFechaInicial2$DdlMinuto'] = $this->formatNumberInt($this->minute_start);
        $post['ctl00$MainContent$CldFechaInicial2$DdlSegundo'] = $this->formatNumberInt($this->second_start);
        $post['ctl00$MainContent$CldFechaFinal2$Calendario_text'] = $this->dayFormat() . '/' . $this->month . '/' . $this->year;
        $post['ctl00$MainContent$CldFechaFinal2$DdlHora'] = $this->formatNumberInt($this->hour_end);
        $post['ctl00$MainContent$CldFechaFinal2$DdlMinuto'] = $this->formatNumberInt($this->minute_end);
        $post['ctl00$MainContent$CldFechaFinal2$DdlSegundo'] = $this->formatNumberInt($this->second_end);
        $post['ctl00$MainContent$DdlEstadoComprobante'] = $this->stateVoucher;
        $post['ctl00$MainContent$FiltroCentral'] = $this->getCentralFilter();
        $post['ctl00$MainContent$TxtRfcReceptor'] = '';
        $post['ctl00$MainContent$TxtUUID'] = $this->taxId;
        $post['ctl00$MainContent$ddlComplementos'] = '-1';
        $post['ctl00$MainContent$hfInicialBool'] = 'false';
        $post['ctl00$ScriptManager1'] = 'ctl00$MainContent$UpnlBusqueda|ctl00$MainContent$BtnBusqueda';

        return $post;
    }

    /**
     * @return array
     */
    public function getFormPostDates()
    {
        $post = [];
        $post['__ASYNCPOST'] = 'true';
        $post['__EVENTARGUMENT'] = '';
        $post['__EVENTTARGET'] = 'ctl00$MainContent$RdoFechas';
        $post['__LASTFOCUS'] = '';
        $post['ctl00$MainContent$CldFechaInicial2$Calendario_text'] = '';
        $post['ctl00$MainContent$CldFechaInicial2$DdlHora'] = '0';
        $post['ctl00$MainContent$CldFechaInicial2$DdlMinuto'] = '0';
        $post['ctl00$MainContent$CldFechaInicial2$DdlSegundo'] = '0';
        $post['ctl00$MainContent$CldFechaFinal2$Calendario_text'] = '';
        $post['ctl00$MainContent$CldFechaFinal2$DdlHora'] = '0';
        $post['ctl00$MainContent$CldFechaFinal2$DdlMinuto'] = '0';
        $post['ctl00$MainContent$CldFechaFinal2$DdlSegundo'] = '0';
        $post['ctl00$MainContent$DdlEstadoComprobante'] = '-1';
        $post['ctl00$MainContent$ddlComplementos'] = '-1';
        $post['ctl00$MainContent$FiltroCentral'] = 'RdoFechas';
        $post['ctl00$MainContent$TxtRfcReceptor'] = '';
        $post['ctl00$MainContent$TxtUUID'] = '';
        $post['ctl00$MainContent$hfInicialBool'] = 'true';
        $post['ctl00$ScriptManager1'] = 'ctl00$MainContent$UpnlBusqueda|ctl00$MainContent$RdoFechas';

        return $post;
    }
}
