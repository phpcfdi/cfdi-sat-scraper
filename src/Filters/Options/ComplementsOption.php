<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters\Options;

use Eclipxe\Enum\Enum;
use PhpCfdi\CfdiSatScraper\Contracts\Filters\FilterOption;

/**
 * This is a common use case enum sample
 * source: tests/Fixtures/Stages.php
 *
 * @method static self todos()
 * @method static self sinComplemento()
 * @method static self acreditamientoIeps()
 * @method static self aerolineas()
 * @method static self certificadoDestruccion()
 * @method static self comercioExterior()
 * @method static self comercioExterior11()
 * @method static self compraVentaDivisas()
 * @method static self consumoCombustibles()
 * @method static self consumoCombustibles11()
 * @method static self donatarias()
 * @method static self estadoCuentaBancario()
 * @method static self estadoCuentaCombustibles12()
 * @method static self estadoCuentaCombustiblesMonederoElectronico()
 * @method static self gastosHidrocarburos()
 * @method static self ine11()
 * @method static self ingresosHidrocarburos()
 * @method static self institucionesEducativasPrivadas()
 * @method static self leyendasFiscales()
 * @method static self misCuentas()
 * @method static self notariosPublicos()
 * @method static self obraArtesAntiguedades()
 * @method static self otrosDerechosImpuestos()
 * @method static self pagoEspecie()
 * @method static self personaFisicaIntegranteCoordinado()
 * @method static self recepcionPagos()
 * @method static self reciboDonativo()
 * @method static self reciboPagoSalarios()
 * @method static self reciboPagoSalarios12()
 * @method static self sectorVentasDetalle()
 * @method static self serviciosConstruccion()
 * @method static self speiTerceroTercero()
 * @method static self sustitucionRenovacionVehicular()
 * @method static self terceros1()
 * @method static self terceros2()
 * @method static self timbreFiscalDigital()
 * @method static self turistaPasajeroExtranjero()
 * @method static self valesDespensa()
 * @method static self vehiculoUsado()
 * @method static self ventaVehiculos()
 *
 * @method bool isTodos()
 * @method bool isSinComplemento()
 * @method bool isAcreditamientoIeps()
 * @method bool isAerolineas()
 * @method bool isCertificadoDestruccion()
 * @method bool isComercioExterior()
 * @method bool isComercioExterior11()
 * @method bool isCompraVentaDivisas()
 * @method bool isConsumoCombustibles()
 * @method bool isConsumoCombustibles11()
 * @method bool isDonatarias()
 * @method bool isEstadoCuentaBancario()
 * @method bool isEstadoCuentaCombustibles12()
 * @method bool isEstadoCuentaCombustiblesMonederoElectronico()
 * @method bool isGastosHidrocarburos()
 * @method bool isIne11()
 * @method bool isIngresosHidrocarburos()
 * @method bool isInstitucionesEducativasPrivadas()
 * @method bool isLeyendasFiscales()
 * @method bool isMisCuentas()
 * @method bool isNotariosPublicos()
 * @method bool isObraArtesAntiguedades()
 * @method bool isOtrosDerechosImpuestos()
 * @method bool isPagoEspecie()
 * @method bool isPersonaFisicaIntegranteCoordinado()
 * @method bool isRecepcionPagos()
 * @method bool isReciboDonativo()
 * @method bool isReciboPagoSalarios()
 * @method bool isReciboPagoSalarios12()
 * @method bool isSectorVentasDetalle()
 * @method bool isServiciosConstruccion()
 * @method bool isSpeiTerceroTercero()
 * @method bool isSustitucionRenovacionVehicular()
 * @method bool isTerceros1()
 * @method bool isTerceros2()
 * @method bool isTimbreFiscalDigital()
 * @method bool isTuristaPasajeroExtranjero()
 * @method bool isValesDespensa()
 * @method bool isVehiculoUsado()
 * @method bool isVentaVehiculos()
 */
class ComplementsOption extends Enum implements FilterOption
{
    protected static function overrideValues(): array
    {
        return [
            'todos' => '-1',
            'sinComplemento' => '8',
            'acreditamientoIeps' => '4294967296',
            'aerolineas' => '8388608',
            'certificadoDestruccion' => '1073741824',
            'comercioExterior' => '17179869184',
            'comercioExterior11' => '274877906944',
            'compraVentaDivisas' => '4',
            'consumoCombustibles' => '16777216',
            'consumoCombustibles11' => '8796093022208',
            'donatarias' => '64',
            'estadoCuentaBancario' => '256',
            'estadoCuentaCombustibles12' => '4398046511104',
            'estadoCuentaCombustiblesMonederoElectronico' => '8589934592',
            'gastosHidrocarburos' => '17592186044416',
            'ine11' => '68719476736',
            'ingresosHidrocarburos' => '35184372088832',
            'institucionesEducativasPrivadas' => '1024',
            'leyendasFiscales' => '4096',
            'misCuentas' => '524288',
            'notariosPublicos' => '67108864',
            'obraArtesAntiguedades' => '536870912',
            'otrosDerechosImpuestos' => '2048',
            'pagoEspecie' => '4194304',
            'personaFisicaIntegranteCoordinado' => '8192',
            'recepcionPagos' => '549755813888',
            'reciboDonativo' => '128',
            'reciboPagoSalarios' => '1048576',
            'reciboPagoSalarios12' => '137438953472',
            'sectorVentasDetalle' => '32',
            'serviciosConstruccion' => '268435456',
            'speiTerceroTercero' => '16384',
            'sustitucionRenovacionVehicular' => '2147483648',
            'terceros1' => '32768',
            'terceros2' => '65536',
            'timbreFiscalDigital' => '2199023255552',
            'turistaPasajeroExtranjero' => '16',
            'valesDespensa' => '33554432',
            'vehiculoUsado' => '134217728',
            'ventaVehiculos' => '2097152',
        ];
    }

    public function nameIndex(): string
    {
        return 'ctl00$MainContent$ddlComplementos';
    }
}
