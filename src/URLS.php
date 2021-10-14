<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

final class URLS
{
    /** @var string The main page to access CFDI Portal */
    public const PORTAL_CFDI = 'https://portalcfdi.facturaelectronica.sat.gob.mx/';

    /** @var string The page to search for received */
    public const PORTAL_CFDI_CONSULTA_RECEPTOR = 'https://portalcfdi.facturaelectronica.sat.gob.mx/ConsultaReceptor.aspx';

    /** @var string The page to search for issued */
    public const PORTAL_CFDI_CONSULTA_EMISOR = 'https://portalcfdi.facturaelectronica.sat.gob.mx/ConsultaEmisor.aspx';

    /** @var string The page to log out */
    public const PORTAL_CFDI_LOGOUT = 'https://portalcfdi.facturaelectronica.sat.gob.mx/logout.aspx';

    /** @var string The authorization page to log in */
    public const AUTH_LOGIN = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';

    /** @var string The authorization page to log in using FIEL */
    public const AUTH_LOGIN_FIEL = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATx509Custom&sid=0&option=credential&sid=0';

    /** @var string The authorization page to log in using CIEC */
    public const AUTH_LOGIN_CIEC = 'https://cfdiau.sat.gob.mx/nidp/wsfed/ep?id=SATUPCFDiCon&sid=0&option=credential&sid=0';
}
