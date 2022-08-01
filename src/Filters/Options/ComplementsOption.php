<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters\Options;

use Eclipxe\MicroCatalog\MicroCatalog;
use PhpCfdi\CfdiSatScraper\Contracts\FilterOption;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;

/**
 * Complement option, as a catalog of complements.
 *
 * @method static self todos()
 * @method static self sinComplemento()
 * @method static self acreditamientoIeps()
 * @method static self aerolineas()
 * @method static self cartaPorte10()
 * @method static self cartaPorte20()
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
 * @method static self recepcionPagos20()
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
 * @method bool isCartaPorte10()
 * @method bool isCartaPorte20()
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
 * @method bool isRecepcionPagos20()
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
 *
 * @method string getInput()
 * @method string getDescription()
 *
 * @extends MicroCatalog<array{input: string, description: string}>
 */
class ComplementsOption extends MicroCatalog implements FilterOption
{
    protected const VALUES = [
        'todos' => [
            'input' => '-1',
            'description' => 'Cualquier complemento',
        ],
        'sinComplemento' => [
            'input' => '8',
            'description' => 'Sin complemento',
        ],
        'acreditamientoIeps' => [
            'input' => '4294967296',
            'description' => 'Acreditamiento de IEPS',
        ],
        'aerolineas' => [
            'input' => '8388608',
            'description' => 'Aerolíneas',
        ],
        'cartaPorte10' => [
            'input' => '70368744177664',
            'description' => 'Carta Porte 1.0',
        ],
        'cartaPorte20' => [
            'input' => '140737488355328',
            'description' => 'Carta Porte 2.0',
        ],
        'certificadoDestruccion' => [
            'input' => '1073741824',
            'description' => 'Certificado de destrucción',
        ],
        'comercioExterior' => [
            'input' => '17179869184',
            'description' => 'Comercio exterior 1.0',
        ],
        'comercioExterior11' => [
            'input' => '274877906944',
            'description' => 'Comercio exterior 1.1',
        ],
        'compraVentaDivisas' => [
            'input' => '4',
            'description' => 'Compra venta de divisas',
        ],
        'consumoCombustibles' => [
            'input' => '16777216',
            'description' => 'Consumo de combustibles 1.0',
        ],
        'consumoCombustibles11' => [
            'input' => '8796093022208',
            'description' => 'Consumo de combustibles 1.1',
        ],
        'donatarias' => [
            'input' => '64',
            'description' => 'Donatarias',
        ],
        'estadoCuentaBancario' => [
            'input' => '256',
            'description' => 'Estado de cuenta bancario',
        ],
        'estadoCuentaCombustibles12' => [
            'input' => '4398046511104',
            'description' => 'Estado de cuenta combustibles 1.2',
        ],
        'estadoCuentaCombustiblesMonederoElectronico' => [
            'input' => '8589934592',
            'description' => 'Estado de cuenta combustibles monedero electrónico',
        ],
        'gastosHidrocarburos' => [
            'input' => '17592186044416',
            'description' => 'Gastos contrato de hidrocarburos',
        ],
        'ine11' => [
            'input' => '68719476736',
            'description' => 'INE 1.1',
        ],
        'ingresosHidrocarburos' => [
            'input' => '35184372088832',
            'description' => 'Ingresos contrato de hidrocarburos',
        ],
        'institucionesEducativasPrivadas' => [
            'input' => '1024',
            'description' => 'Instituciones educativas privadas',
        ],
        'leyendasFiscales' => [
            'input' => '4096',
            'description' => 'Leyendas fiscales',
        ],
        'misCuentas' => [
            'input' => '524288',
            'description' => 'Mis cuentas',
        ],
        'notariosPublicos' => [
            'input' => '67108864',
            'description' => 'Notarios públicos',
        ],
        'obraArtesAntiguedades' => [
            'input' => '536870912',
            'description' => 'Obras de artes plásticas y antigüedades',
        ],
        'otrosDerechosImpuestos' => [
            'input' => '2048',
            'description' => 'Otros derechos e impuestos',
        ],
        'pagoEspecie' => [
            'input' => '4194304',
            'description' => 'Pago en especie',
        ],
        'personaFisicaIntegranteCoordinado' => [
            'input' => '8192',
            'description' => 'Personas físicas integrantes de coordinados',
        ],
        'recepcionPagos' => [
            'input' => '549755813888',
            'description' => 'Recepción de pagos',
        ],
        'recepcionPagos20' => [
            'input' => '1125899906842624',
            'description' => 'Recepción de pagos 2.0',
        ],
        'reciboDonativo' => [
            'input' => '128',
            'description' => 'Recibo de donativo',
        ],
        'reciboPagoSalarios' => [
            'input' => '1048576',
            'description' => 'Nómina 1.1',
        ],
        'reciboPagoSalarios12' => [
            'input' => '137438953472',
            'description' => 'Nómina 1.2',
        ],
        'sectorVentasDetalle' => [
            'input' => '32',
            'description' => 'Facturas del sector de ventas al detalle',
        ],
        'serviciosConstruccion' => [
            'input' => '268435456',
            'description' => 'Servicios parciales de construcción',
        ],
        'speiTerceroTercero' => [
            'input' => '16384',
            'description' => 'SPEI Tercero a Tercero',
        ],
        'sustitucionRenovacionVehicular' => [
            'input' => '2147483648',
            'description' => 'Renovación y sustitución vehicular',
        ],
        'terceros1' => [
            'input' => '32768',
            'description' => 'Concepto por cuenta de terceros 1.1',
        ],
        'terceros2' => [
            'input' => '65536',
            'description' => 'Concepto por cuenta de terceros 2.0',
        ],
        'timbreFiscalDigital' => [
            'input' => '2199023255552',
            'description' => 'Timbre Fiscal Digital',
        ],
        'turistaPasajeroExtranjero' => [
            'input' => '16',
            'description' => 'Turista pasajero extranjero',
        ],
        'valesDespensa' => [
            'input' => '33554432',
            'description' => 'Vales de despensa',
        ],
        'vehiculoUsado' => [
            'input' => '134217728',
            'description' => 'Vehiculos usados',
        ],
        'ventaVehiculos' => [
            'input' => '2097152',
            'description' => 'Venta de vehiculos nuevos',
        ],
    ];

    /**
     * @param string $name
     * @param mixed $arguments
     * @return self
     */
    public static function __callStatic(string $name, $arguments)
    {
        return new self($name);
    }

    public static function getEntriesArray(): array
    {
        return self::VALUES;
    }

    public function getEntryValueOnUndefined(): array
    {
        throw InvalidArgumentException::complementsOptionInvalidKey((string) $this->getEntryIndex());
    }

    public function nameIndex(): string
    {
        return 'ctl00$MainContent$ddlComplementos';
    }

    public function value(): string
    {
        return $this->getInput();
    }
}
