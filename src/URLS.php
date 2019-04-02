<?php

namespace PhpCfdi\CfdiSatScraper;

final class URLS
{
    const SAT_URL_LOGIN = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';
    const SAT_URL_CAPTCHA = 'https://cfdiau.sat.gob.mx/nidp/jcaptcha.jpg';
    const SAT_HOST_CFDI_AUTH = 'cfdiau.sat.gob.mx';
    const SAT_HOST_PORTAL_CFDI = 'portalcfdi.facturaelectronica.sat.gob.mx';
    const SAT_URL_PORTAL_CFDI = 'https://portalcfdi.facturaelectronica.sat.gob.mx/';
    const SAT_URL_WSFEDERATION = 'https://cfdicontribuyentes.accesscontrol.windows.net/v2/wsfederation';
    const SAT_URL_PORTAL_CFDI_CONSULTA = 'https://portalcfdi.facturaelectronica.sat.gob.mx/Consulta.aspx';
    const SAT_URL_PORTAL_CFDI_CONSULTA_RECEPTOR = 'https://portalcfdi.facturaelectronica.sat.gob.mx/ConsultaReceptor.aspx';
    const SAT_URL_PORTAL_CFDI_CONSULTA_EMISOR = 'https://portalcfdi.facturaelectronica.sat.gob.mx/ConsultaEmisor.aspx';
}
