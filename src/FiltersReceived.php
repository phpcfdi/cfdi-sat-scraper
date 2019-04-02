<?php

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Contracts\Filters;

/**
 * Class FiltersReceived.
 */
class FiltersReceived extends BaseFilters implements Filters
{
    /**
     * FiltersReceived constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

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
        $post['ctl00$MainContent$CldFecha$DdlAnio'] = $this->year;
        $post['ctl00$MainContent$CldFecha$DdlMes'] = $this->formatNumberInt($this->month);
        $post['ctl00$MainContent$CldFecha$DdlDia'] = $this->dayFormat();
        $post['ctl00$MainContent$CldFecha$DdlHora'] = $this->formatNumberInt($this->hour_start);
        $post['ctl00$MainContent$CldFecha$DdlMinuto'] = $this->formatNumberInt($this->minute_start);
        $post['ctl00$MainContent$CldFecha$DdlSegundo'] = $this->formatNumberInt($this->second_start);
        $post['ctl00$MainContent$CldFecha$DdlHoraFin'] = $this->formatNumberInt($this->hour_end);
        $post['ctl00$MainContent$CldFecha$DdlMinutoFin'] = $this->formatNumberInt($this->minute_end);
        $post['ctl00$MainContent$CldFecha$DdlSegundoFin'] = $this->formatNumberInt($this->second_end);
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
        $post['ctl00$MainContent$CldFecha$DdlAnio'] = @date('Y');
        $post['ctl00$MainContent$CldFecha$DdlDia'] = '0';
        $post['ctl00$MainContent$CldFecha$DdlHora'] = '0';
        $post['ctl00$MainContent$CldFecha$DdlHoraFin'] = '23';
        $post['ctl00$MainContent$CldFecha$DdlMes'] = '1';
        $post['ctl00$MainContent$CldFecha$DdlMinuto'] = '0';
        $post['ctl00$MainContent$CldFecha$DdlMinutoFin'] = '59';
        $post['ctl00$MainContent$CldFecha$DdlSegundo'] = '0';
        $post['ctl00$MainContent$CldFecha$DdlSegundoFin'] = '59';
        $post['ctl00$MainContent$DdlEstadoComprobante'] = '-1';
        $post['ctl00$MainContent$FiltroCentral'] = 'RdoFechas';
        $post['ctl00$MainContent$TxtRfcReceptor'] = '';
        $post['ctl00$MainContent$TxtUUID'] = '';
        $post['ctl00$MainContent$ddlComplementos'] = '-1';
        $post['ctl00$MainContent$hfInicialBool'] = 'true';
        $post['ctl00$ScriptManager1'] = 'ctl00$MainContent$UpnlBusqueda|ctl00$MainContent$RdoFechas';

        return $post;
    }
}
