<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

final class URLS
{
    public const SAT_URL_LOGIN = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';

    public const SAT_URL_PORTAL_CFDI = 'https://portalcfdi.facturaelectronica.sat.gob.mx/';

    public const SAT_URL_PORTAL_CFDI_CONSULTA_RECEPTOR = 'https://portalcfdi.facturaelectronica.sat.gob.mx/ConsultaReceptor.aspx';

    public const SAT_URL_PORTAL_CFDI_CONSULTA_EMISOR = 'https://portalcfdi.facturaelectronica.sat.gob.mx/ConsultaEmisor.aspx';

    public const SAT_URL_LOGOUT = 'https://portalcfdi.facturaelectronica.sat.gob.mx/logout.aspx';
}
