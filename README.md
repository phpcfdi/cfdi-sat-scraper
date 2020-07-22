# phpcfdi/cfdi-sat-scraper

[![Source Code][badge-source]][source]
[![Discord][badge-discord]][discord]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Build Status][badge-build]][build]
[![Scrutinizer][badge-quality]][quality]
[![Coverage Status][badge-coverage]][coverage]
[![Total Downloads][badge-downloads]][downloads]

Obtiene las facturas emitidas, recibidas, vigentes y cancelados por medio de web scraping desde la página del SAT.

**Importante:**
Actualmente no hay liberada una versión estable, una vez liberada se utilizará SEMVER para actualizar de forma segura.

## Instalacion por composer

```shell
composer require phpcfdi/cfdi-sat-scraper:master-dev
```

## Funcionamiento

El servicio de descarga de CFDI del SAT que se encuentra en <https://portalcfdi.facturaelectronica.sat.gob.mx/>
requiere identificarse con RFC, Clave CIEC y de la resolución de un *captcha*.

Una vez dentro del sitio se pueden consultar facturas emitidas y facturas recibidas. Ya sea por UUID o por filtro.

- Criterios:
    - Tipo: Emitidas o recibidas.
    - Filtro: UUID o consulta.

- Consulta de emitidas:
    - Fecha y hora de emisión.
    - Fecha y hora de recepción.
    - RFC Receptor.
    - Estado del comprobante (cualquiera, vigente o cancelado).
    - Tipo de comprobante (si contiene un complemento específico).

- Consulta de recibidas:
    - Fecha de emisión.
    - Hora inicial y hora final (dentro de la fecha de emisión).
    - RFC Emisor.
    - Estado del comprobante (cualquiera, vigente o cancelado).
    - Tipo de comprobante (si contiene un complemento específico).

El servicio de búsqueda regresa una tabla con información, con un tope de 500 registros por consulta
(aun cuando existan más, solo se muestran 500).

Una vez con el listado el sitio ofrece ligas para poder descargar el archivo XML del CFDI.

## Implementación del funcionamiento del sitio en la librería

El objeto principal de trabajo se llama `SatScraper` con el que se pueden realizar consultas por rango de fecha o
por UUIDS específicos y obtener resultados.
La consulta por UUID (uno o varios) se ejecuta con el método `listByUuids` y el resultado es un `MetadataList`.
La consulta por filtros se llama `QueryByFilters`, se ejecuta con los métodos `listByPeriod` y `listByDateTime` y el resultado es un `MetadataList`.

Una vez con los resultados `MetadataList` se puede solicitar una descarga a una carpeta específica o bien por medio
de un objeto *handler*. El proceso de descarga permite hacer varias descargas en forma simultánea.

Para generar los resultados del `MetadataList` la librería cuenta con una estrategia de división.
Si se trata de una consulta de CFDI por filtros automáticamente se divide por día.
En caso de que en el periodo consultado se encuentren 500 o más registros entonces la búsqueda se va subdividiendo
en diferentes periodos, hasta llegar a la consulta mínima de 1 segundo. Luego los resultados son nuevamente unidos.

Una vez que tienes un listado `MetadataList` se puede aplicar un filtro para obtener un nuevo listado con únicamente
los objetos `Metadata` donde el UUID coincide.

Los métodos para ejecutar la descarga de metadata son:

- Por UUID: `SatScraper::listByUuids(string[] $uuids, DownloadType $type): MetadataList`
- Por filtros con días completos: `SatScraper::listByPeriod(Query $query): MetadataList`
- Por filtros con fechas exactas: `SatScraper::listByDateTime(Query $query): MetadataList`

Y una vez con el `MetadataList` se crea un objeto descargador y se le pide que ejecute las descargas.

- Creación: `SatScraper::xmlDownloader(MetadataList $list = null, int $concurrency = 10): XmlDownloader`
- Guardar a una carpeta: `XmlDownloader::saveTo(string $destination): void`
- Guardar con un manejador: `XmlDownloader::download(XmlDownloadHandlerInterface $handler): void`

Si se llega a la consulta mínima de 1 segundo y se obtuvieron 500 o más registros entonces adicionalmente
se llama a un *callback* (opcional) para reportar este hecho.

No contamos con un método propio para resolver captchas, pero se puede utilizar un servicio externo como *DeCaptcher*.
Si cuentas con un servicio diferente solo debes implementar la interfaz `CaptchaResolverInterface`.
Aceptamos PR de nuevas implementaciones. La recomendación es crear un paquete diferente que permita
conectarse con un servicio externo, por ejemplo: `tu-vendor/cfdi-sat-scraper-mi-servicio-captcha-resolver`.

Esta librería está basada en [Guzzle](https://github.com/guzzle/guzzle), por lo que puedes configurar el cliente
a tus propias necesidades como configurar un proxy o depurar las llamadas HTTP.
Gracias a esta librería podemos ofrecer descargas simultáneas de XML.

La búsqueda siempre debe crearse con un rango de fechas, además en forma predeterminada, se busca por CFDI emitidos,
con cualquier complemento y con cualquier estado (vigente o cancelado). Sin embargo puedes cambiar la búsqueda antes
de enviar a procesarla.

```php
<?php declare(strict_types=1);

use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\Filters\Options\ComplementsOption;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\RfcOption;

// se crea con un rango de fechas específico
$query = new QueryByFilters(new DateTimeImmutable('2019-03-01'), new DateTimeImmutable('2019-03-31'));
$query
    ->setDownloadType(DownloadType::recibidos())                // en lugar de emitidos
    ->setStateVoucher(StatesVoucherOption::vigentes())          // en lugar de todos
    ->setRfc(new RfcOption('EKU9003173C9'))                     // de este RFC específico
    ->setComplement(ComplementsOption::reciboPagoSalarios12())  // que incluya este complemento
;
```

## Ejemplo de descarga por rango de fechas

```php
<?php declare(strict_types=1);

use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\SatSessionData;

/** @var CaptchaResolverInterface $captchaResolver */
$satScraper = new SatScraper(new SatSessionData('rfc', 'ciec', $captchaResolver));

$query = new QueryByFilters(new DateTimeImmutable('2019-03-01'), new DateTimeImmutable('2019-03-31'));
$list = $satScraper->listByPeriod($query);

// impresión de cada uno de los metadata
foreach ($list as $cfdi) {
    echo 'UUID: ', $cfdi->uuid(), PHP_EOL;
    echo 'Emisor: ', $cfdi->get('rfcEmisor'), ' - ', $cfdi->get('nombreEmisor'), PHP_EOL;
    echo 'Receptor: ', $cfdi->get('rfcReceptor'), ' - ', $cfdi->get('nombreReceptor'), PHP_EOL;
    echo 'Fecha: ', $cfdi->get('fechaEmision'), PHP_EOL;
    echo 'Tipo: ', $cfdi->get('efectoComprobante'), PHP_EOL;
    echo 'Estado: ', $cfdi->get('estadoComprobante'), PHP_EOL;
}

// descarga de cada uno de los CFDI, reporta los descargados en $downloadedUuids
$downloadedUuids = $satScraper->xmlDownloader($list)
    ->setConcurrency(50)                            // cambiar a 50 descargas simultáneas
    ->saveTo('/storage/downloads');                 // ejecutar la instrucción de descarga
echo json_encode($downloadedUuids);
```

## Ejemplo de descarga por lista de UUIDS

```php
<?php declare(strict_types=1);

use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\SatSessionData;

/** @var CaptchaResolverInterface $captchaResolver */
$satScraper = new SatScraper(new SatSessionData('rfc', 'ciec', $captchaResolver));

$uuids = [
    '5cc88a1a-8672-11e6-ae22-56b6b6499611',
    '5cc88c4a-8672-11e6-ae22-56b6b6499612',
    '5cc88d4e-8672-11e6-ae22-56b6b6499613'
];
$list = $satScraper->listByUuids($uuids, DownloadType::recibidos());
echo json_encode($list);
```

## Aviso de que existen más de 500 comprobantes en un mismo segundo

El servicio ofrecido por el SAT tiene límites, entre ellos, no se pueden obtener más de 500 registros
en un rango de fechas. Esta librería trata de reducir el rango para obtener todos los datos, sin embargo,
si se presenta que en un mismo segundo existen 500 o más CFDI, entonces se puede invocar una función
que le puede ayudar a considerar este escenario.

```php
<?php declare(strict_types=1);

use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\SatSessionData;

// define onFiveHundred callback
$onFiveHundred = function (DateTimeImmutable $date) {
    echo 'Se encontraron más de 500 CFDI en el segundo: ', $date->format('c'), PHP_EOL;
};

// create scraper using the callback
/**
 * @var SatSessionData $sessionData
 * @var SatHttpGateway $httpGateway
 */
$satScraper = new SatScraper($sessionData, $httpGateway, $onFiveHundred);

$query = new QueryByFilters(new DateTimeImmutable('2019-03-01'), new DateTimeImmutable('2019-03-31'));
$list = $satScraper->listByPeriod($query);
echo json_encode($list);
```

## Descargar CFDIS a una carpeta

Ejecutar el método `saveTo` devuelve un arreglo con los UUID que fueron efectivamente descargados.

Si ocurrió un error con alguna de las descargas dicho error será ignorado.

```php
<?php declare(strict_types=1);

use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\SatSessionData;

/** @var CaptchaResolverInterface $captchaResolver */
$satScraper = new SatScraper(new SatSessionData('rfc', 'ciec', $captchaResolver));

$query = new QueryByFilters(new DateTimeImmutable('2019-03-01'), new DateTimeImmutable('2019-03-31'));
$list = $satScraper->listByPeriod($query);

// $downloadedUuids contiene un listado de UUID que fueron procesados correctamente, 50 descargas simultáneas
$downloadedUuids = $satScraper->xmlDownloader($list, 50)
    ->saveTo('/storage/downloads', true, 0777);
echo json_encode($downloadedUuids);
```

## Procesar de forma personalizada cada descarga de CFDI

Ejecutar el método `download` devuelve un arreglo con los UUID que fueron efectivamente descargados.
Y permite configurar los eventos de descarga y manejo de errores.

Si se desea ignorar los errores se puede simplemente especificar el método `XmlDownloadHandlerInterface::onError()`
sin contenido, entonces el error solamente se perderá. De todas maneras, gracias a que el método `download`
devuelve un arreglo de UUID con los que fueron efectivamente descargados entonces se puede filtrar el `MetadataList`
para extraer aquellos que no fueron descargados.

Vea la clase `PhpCfdi\CfdiSatScraper\Internal\XmlDownloadStoreInFolder` como ejemplo de implementación
de la interfaz `XmlDownloadHandlerInterface`.

```php
<?php declare(strict_types=1);

use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Contracts\XmlDownloadHandlerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\XmlDownloadError;
use PhpCfdi\CfdiSatScraper\Exceptions\XmlDownloadResponseError;
use PhpCfdi\CfdiSatScraper\Exceptions\XmlDownloadRequestExceptionError;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\SatSessionData;
use Psr\Http\Message\ResponseInterface;

/** @var CaptchaResolverInterface $captchaResolver */
$satScraper = new SatScraper(new SatSessionData('rfc', 'ciec', $captchaResolver));

$query = new QueryByFilters(new DateTimeImmutable('2019-03-01'), new DateTimeImmutable('2019-03-31'));

$list = $satScraper->listByPeriod($query);

$myHandler = new class implements XmlDownloadHandlerInterface {
    public function onSuccess(string $uuid, string $content, ResponseInterface $response): void
    {
        $filename = '/storage/' . $uuid . '.xml';
        echo 'Saving ', $uuid, PHP_EOL;
        file_put_contents($filename, (string) $response->getBody());
    }
    public function onError(XmlDownloadError $error) : void
    {
        if ($error instanceof XmlDownloadRequestExceptionError) {
            echo "Error getting {$error->getUuid()} from {$error->getReason()->getRequest()->getUri()}\n";
        } elseif ($error instanceof XmlDownloadResponseError) {
            echo "Error getting {$error->getUuid()}, invalid response: {$error->getMessage()}\n";
            $response = $error->getReason(); // reason is a ResponseInterface
            print_r(['headers' => $response->getHeaders(), 'body' => $response->getBody()]);
        } else { // XmlDownloadError
            echo "Error getting {$error->getUuid()}, reason: {$error->getMessage()}\n";
            print_r(['reason' => $error->getReason()]);
        }
    }
};

// $downloadedUuids contiene un listado de UUID que fueron procesados correctamente
$downloadedUuids = $satScraper->xmlDownloader($list)->download($myHandler);
echo json_encode($downloadedUuids);
```

## Verificar datos de autenticación sin hacer una consulta

El siguiente ejemplo muestra cómo usar el método `SatScraper::confirmSessionIsAlive` para verificar que
los datos de sesión sean (o continuen siendo) correctos. El funcionamiento interno del scraper es:
Si la sesión no se inicializó previamente entonces se intentará hacer el proceso de autenticación,
además se verificará que la sesión (`cookie`) se encuentre vigente.

Se hacen los dos pasos para evitar consumir el servicio de resolución de captcha en forma innecesaria.

```php
<?php declare(strict_types=1);

use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\SatSessionData;

/** @var CaptchaResolverInterface $captchaResolver */
$satScraper = new SatScraper(new SatSessionData('rfc', 'ciec', $captchaResolver));
try {
    $satScraper->confirmSessionIsAlive();
} catch (LoginException $exception) {
    echo 'ERROR: ', $exception->getMessage(), PHP_EOL;
    return;
}
```

## Quitar la verificación de certificados del SAT

En caso de que los certificados del SAT usados en HTTPS fallen, será necerario que desactive la verificación
de los mismos. Esto se puede lograr creando el cliente de Guzzle con la negación de la opción `verify`.

No es una práctica recomendada pero tal vez necesaria ante los problemas a los que el SAT se ve expuesto.
Considera que esto podría facilitar significativamente un ataque que provoque que la pérdida de su clave CIEC.

```php
<?php declare(strict_types=1);
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\SatSessionData;

$insecureClient = new Client([
    RequestOptions::VERIFY => false
]);
$gateway = new SatHttpGateway($insecureClient);

/** @var SatSessionData $sessionData */
$scraper = new SatScraper($sessionData, $gateway);
```

## Compatilibilidad

Esta librería se mantendrá compatible con al menos la versión con
[soporte activo de PHP](https://www.php.net/supported-versions.php) más reciente.

También utilizamos [Versionado Semántico 2.0.0](https://semver.org/lang/es/)
por lo que puedes usar esta librería sin temor a romper tu aplicación.

## Contribuciones

Las contribuciones con bienvenidas. Por favor lee [CONTRIBUTING][] para más detalles
y recuerda revisar el archivo de tareas pendientes [TODO][] y el [CHANGELOG][].

Documentación de desarrollo:

  - [Entorno de desarrollo](https://github.com/phpcfdi/cfdi-sat-scraper/blob/master/development/docs/EntornoDesarrollo.md)
  - [Integración contínua](https://github.com/phpcfdi/cfdi-sat-scraper/blob/master/development/docs/IntegracionContinua.md)
  - [Test de integración](https://github.com/phpcfdi/cfdi-sat-scraper/blob/master/development/docs/TestIntegracion.md)

## Copyright and License

The `phpcfdi/cfdi-sat-scraper` library is copyright © [PhpCfdi](https://www.phpcfdi.com)
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for more information.

[contributing]: https://github.com/phpcfdi/cfdi-sat-scraper/blob/master/CONTRIBUTING.md
[changelog]: https://github.com/phpcfdi/cfdi-sat-scraper/blob/master/docs/CHANGELOG.md
[todo]: https://github.com/phpcfdi/cfdi-sat-scraper/blob/master/docs/TODO.md

[source]: https://github.com/phpcfdi/scfdi-sat-scraper
[discord]: https://discord.gg/aFGYXvX
[release]: https://github.com/phpcfdi/cfdi-sat-scraper/releases
[license]: https://github.com/phpcfdi/cfdi-sat-scraper/blob/master/LICENSE
[build]: https://travis-ci.com/phpcfdi/cfdi-sat-scraper?branch=master
[quality]: https://scrutinizer-ci.com/g/phpcfdi/cfdi-sat-scraper/
[coverage]: https://scrutinizer-ci.com/g/phpcfdi/cfdi-sat-scraper/code-structure/master/code-coverage/src/
[downloads]: https://packagist.org/packages/phpcfdi/cfdi-sat-scraper

[badge-source]: https://img.shields.io/badge/source-phpcfdi/cfdi--sat--scraper-blue?style=flat-square
[badge-discord]: https://img.shields.io/discord/459860554090283019?style=flat-square
[badge-release]: https://img.shields.io/github/release/phpcfdi/cfdi-sat-scraper?style=flat-square
[badge-license]: https://img.shields.io/github/license/phpcfdi/cfdi-sat-scraper?style=flat-square
[badge-build]: https://img.shields.io/travis/com/phpcfdi/cfdi-sat-scraper/master?style=flat-square
[badge-quality]: https://img.shields.io/scrutinizer/g/phpcfdi/cfdi-sat-scraper/master?style=flat-square
[badge-coverage]: https://img.shields.io/scrutinizer/coverage/g/phpcfdi/cfdi-sat-scraper/master?style=flat-square
[badge-downloads]: https://img.shields.io/packagist/dt/phpcfdi/cfdi-sat-scraper?style=flat-square
