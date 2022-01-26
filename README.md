# phpcfdi/cfdi-sat-scraper

[![Source Code][badge-source]][source]
[![Packagist PHP Version Support][badge-php-version]][php-version]
[![Discord][badge-discord]][discord]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Build Status][badge-build]][build]
[![Reliability][badge-reliability]][reliability]
[![Maintainability][badge-maintainability]][maintainability]
[![Code Coverage][badge-coverage]][coverage]
[![Violations][badge-violations]][violations]
[![Total Downloads][badge-downloads]][downloads]

Obtiene las facturas emitidas, recibidas, vigentes y cancelados por medio de web scraping desde la página del SAT.
Los recursos descargables son los archivos XML de CFDI y los archivos PDF de representación impresa,
solicitud de cancelación y acuse de cancelación.

## Instalacion por composer

```shell
composer require phpcfdi/cfdi-sat-scraper
```

## Funcionamiento

El servicio de descarga de CFDI del SAT que se encuentra en la dirección <https://portalcfdi.facturaelectronica.sat.gob.mx/>,
requiere identificarse con RFC, Clave CIEC y de la resolución de un *captcha*,
o bien, utilizando el certificado y llave privada FIEL.

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
La consulta por filtros se llama `QueryByFilters`, se ejecuta con los métodos `listByPeriod` y `listByDateTime`
y el resultado es un `MetadataList`.

Para generar los resultados del `MetadataList` la librería cuenta con una estrategia de división.
Si se trata de una consulta de CFDI por filtros automáticamente se divide por día.
En caso de que en el periodo consultado se encuentren 500 o más registros entonces la búsqueda se va subdividiendo
en diferentes periodos, hasta llegar a la consulta mínima de 1 segundo. Luego los resultados son nuevamente unidos.

Una vez que tienes un listado `MetadataList` se puede aplicar un filtro para obtener un nuevo listado con únicamente
los objetos `Metadata` donde el UUID coincide; o bien, usar otros filtros como solo los que contienen un determinado
recurso descargable.

Una vez con los resultados `MetadataList` se puede solicitar una descarga a una carpeta específica o bien por medio
de un objeto *handler*. El proceso de descarga permite hacer varias descargas en forma simultánea.

La descarga puede ser de archivos de:

- Archivos de CFDI (XML).
- Representación impresa del CFDI (PDF).
- Solicitud de cancelación (PDF).
- Acuse de cancelación (PDF).

Los métodos para ejecutar la descarga de metadata son:

- Por UUID: `SatScraper::listByUuids(string[] $uuids, DownloadType $type): MetadataList`
- Por filtros con días completos: `SatScraper::listByPeriod(Query $query): MetadataList`
- Por filtros con fechas exactas: `SatScraper::listByDateTime(Query $query): MetadataList`

Y una vez con el objeto `MetadataList` se crea un objeto descargador de recursos `ResourceDownloader`
y se le pide que ejecute las descargas por tipo de recurso.

- Creación: `SatScraper::resourceDownloader(ResourceType $resourceType, MetadataList $list = null, int $concurrency = 10): ResourceDownloader`
- Guardar a una carpeta: `ResourceDownloader::saveTo(string $destination): void`
- Guardar con un manejador: `ResourceDownloader::download(ResourceDownloadHandlerInterface $handler): void`

Si se llega a la consulta mínima de 1 segundo y se obtuvieron 500 o más registros entonces adicionalmente
se llama a un *callback* (opcional) para reportar este acontecimiento.

La búsqueda siempre debe crearse con un rango de fechas, además en forma predeterminada, se busca por CFDI emitidos,
con cualquier complemento y con cualquier estado (vigente o cancelado). Sin embargo puedes cambiar la búsqueda antes
de enviar a procesarla.

Esta librería está basada en [Guzzle](https://github.com/guzzle/guzzle), por lo que puedes configurar el cliente
a tus propias necesidades como configurar un proxy o depurar las llamadas HTTP.
Gracias a esta librería podemos ofrecer descargas simultáneas de XML y hacer el proceso de comunicación mucho
más veloz que si se estuviera utilizando un navegador completo.

## Autenticación

Esta librería permite identificarse ante el SAT utilizando alguno de dos mecanismos: Clave CIEC o FIEL.

### Autenticación por FIEL

Para identificarse utilizando la FIEL se necesita usar el manejador de sesiones `FielSessionManager`,
con el respectivo certificado, llave privada y contraseña de la llave privada.

La ventaja de este método es que no requiere de un resolvedor de captchas.
La desventaja es que es riesgoso trabajar con la FIEL.

Advertencia: No utilice este mecanismo a menos que se trate de su propia FIEL.
La FIEL en México está regulada por la "Ley de Firma Electrónica Avanzada".
Su uso es extenso y no está limitado al SAT, con ella se pueden realizar múltiples operaciones legales.
En PhpCfdi no recomendamos que almacene o use la FIEL de terceras personas.

### Autenticación por clave CIEC

Para identificarse utilizando la clave CIEC se necesita usar el manejador de sesiones `CiecSessionManager`,
con los datos de RFC, Clave CIEC y un resolvedor de captchas.

La ventaja de este método es que no se requiere la FIEL.
La desventaja es que se requiere un resolvedor de captchas.

No contamos con un método propio para resolver los captchas, pero se puede utilizar un servicio externo como
[*Anti-Captcha*](https://anti-captcha.com). Para testeo o implementaciones locales puedes
usar [`eclipxe/captcha-local-resolver](https://github.com/eclipxe13/captcha-local-resolver)
donde tú mismo serás el que resuelve los captchas, las tres implementaciones están creadas.

La resolución de captchas se realiza a través de la librería de resolución de captchas
[`phpcfdi/image-captcha-resolver`](https://github.com/phpcfdi/image-captcha-resolver).
Si estás usando un servicio que no está implementado puedes revisar la documentación
de este proyecto e integrar el servicio dentro de los clientes soportados.

## Ejemplo de elaboración de consulta

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

use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionManager;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;

/** @var CaptchaResolverInterface $captchaResolver */
$satScraper = new SatScraper(CiecSessionManager::create('rfc', 'ciec', $captchaResolver));

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
$downloadedUuids = $satScraper->resourceDownloader(ResourceType::xml(), $list)
    ->setConcurrency(50)                            // cambiar a 50 descargas simultáneas
    ->saveTo('/storage/downloads');                 // ejecutar la instrucción de descarga
echo json_encode($downloadedUuids);
```

## Ejemplo de descarga por lista de UUIDS

```php
<?php declare(strict_types=1);

use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionManager;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;

/** @var CaptchaResolverInterface $captchaResolver */
$satScraper = new SatScraper(CiecSessionManager::create('rfc', 'ciec', $captchaResolver));

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
en un rango de fechas. Esta librería trata de reducir el rango hasta el mínimo de una consulta en un segundo
para obtener todos los datos, sin embargo, si se presenta este caso, entonces se puede invocar a un manejador
que le puede ayudar a registrar este escenario.

Si al crear el objeto `SatScraper` no se establece un manejador o se establece como `null` entonces se usará
una instancia de `NullMaximumRecordsHandler` que, como su nombre lo indica, no realiza ninguna acción.

```php
<?php declare(strict_types=1);

use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;

// define handler
$handler = new class () implements MaximumRecordsHandler {
    public function handle(DateTimeImmutable $date): void
    {
        echo 'Se encontraron más de 500 CFDI en el segundo: ', $date->format('c'), PHP_EOL;
    }
};

// create scraper using the handler
/**
 * @var SessionManager $sessionManager
 * @var SatHttpGateway $httpGateway
 */
$satScraper = new SatScraper($sessionManager, $httpGateway, $handler);

$query = new QueryByFilters(new DateTimeImmutable('2019-03-01'), new DateTimeImmutable('2019-03-31'));
$list = $satScraper->listByPeriod($query);
echo json_encode($list);
```

## Descargar CFDIS a una carpeta

Ejecutar el método `saveTo` devuelve un arreglo con los UUID que fueron efectivamente descargados.

Si ocurrió un error con alguna de las descargas dicho error será ignorado.

```php
<?php declare(strict_types=1);

use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionManager;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;

/** @var CaptchaResolverInterface $captchaResolver */
$satScraper = new SatScraper(CiecSessionManager::create('rfc', 'ciec', $captchaResolver));

$query = new QueryByFilters(new DateTimeImmutable('2019-03-01'), new DateTimeImmutable('2019-03-31'));
$list = $satScraper->listByPeriod($query);

// $downloadedUuids contiene un listado de UUID que fueron procesados correctamente, 50 descargas simultáneas
$downloadedUuids = $satScraper->resourceDownloader(ResourceType::xml(), $list, 50)
    ->saveTo('/storage/downloads', true, 0777);
echo json_encode($downloadedUuids);
```

De manera predeterminada, los archivos son almacenados en la carpeta como:

- CFDI: `uuid` + `.xml`.
- Representación impresa: `uuid` + `.pdf`.
- Solicitud de cancelacion: `uuid` + `-cancel-request.pdf`.
- Acuse de cancelacion: `uuid` + `-cancel-voucher.pdf`.

Para cambiar los nombres de archivos, cree una implementacion de la interfaz `\PhpCfdi\CfdiSatScraper\Contracts\ResourceFileNamerInterface`
y configura el descargador de recursos con el método `ResourceDownloader::setResourceFileNamer()`.

## Procesar de forma personalizada cada descarga de CFDI

Ejecutar el método `ResourceDownloader::download` devuelve un arreglo con los UUID que fueron efectivamente descargados.
Y permite configurar los eventos de descarga y manejo de errores.

Si se desea ignorar los errores se puede simplemente especificar el método `ResourceDownloadHandlerInterface::onError()`
sin contenido, entonces el error solamente se perderá. De todas maneras, gracias a que el método `download`
devuelve un arreglo de UUID con los que fueron efectivamente descargados entonces se puede filtrar
el objeto `MetadataList` para extraer aquellos que no fueron descargados.

Vea la clase `PhpCfdi\CfdiSatScraper\Internal\ResourceDownloadStoreInFolder` como ejemplo de implementación
de la interfaz `ResourceDownloadHandlerInterface`.

```php
<?php declare(strict_types=1);

use PhpCfdi\CfdiSatScraper\Contracts\ResourceDownloadHandlerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadError;
use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadResponseError;
use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadRequestExceptionError;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionManager;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use Psr\Http\Message\ResponseInterface;

/** @var CaptchaResolverInterface $captchaResolver */
$satScraper = new SatScraper(CiecSessionManager::create('rfc', 'ciec', $captchaResolver));

$query = new QueryByFilters(new DateTimeImmutable('2019-03-01'), new DateTimeImmutable('2019-03-31'));

$list = $satScraper->listByPeriod($query);

$myHandler = new class implements ResourceDownloadHandlerInterface {
    public function onSuccess(string $uuid, string $content, ResponseInterface $response): void
    {
        $filename = '/storage/' . $uuid . '.xml';
        echo 'Saving ', $uuid, PHP_EOL;
        file_put_contents($filename, (string) $response->getBody());
    }

    public function onError(ResourceDownloadError $error) : void
    {
        if ($error instanceof ResourceDownloadRequestExceptionError) {
            echo "Error getting {$error->getUuid()} from {$error->getReason()->getRequest()->getUri()}\n";
        } elseif ($error instanceof ResourceDownloadResponseError) {
            echo "Error getting {$error->getUuid()}, invalid response: {$error->getMessage()}\n";
            $response = $error->getReason(); // reason is a ResponseInterface
            print_r(['headers' => $response->getHeaders(), 'body' => $response->getBody()]);
        } else { // ResourceDownloadError
            echo "Error getting {$error->getUuid()}, reason: {$error->getMessage()}\n";
            print_r(['reason' => $error->getReason()]);
        }
    }
};

// $downloadedUuids contiene un listado de UUID que fueron procesados correctamente
$downloadedUuids = $satScraper->resourceDownloader(ResourceType::xml(), $list)->download($myHandler);
echo json_encode($downloadedUuids);
```

## Usar el servicio *Anti-Captcha*

```php
<?php declare(strict_types=1);

use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionManager;
use PhpCfdi\ImageCaptchaResolver\Resolvers\AntiCaptchaResolver;

$captchaResolver = AntiCaptchaResolver::create('anticaptcha-client-key');

$satScraper = new SatScraper(CiecSessionManager::create('rfc', 'ciec', $captchaResolver));
```

## Verificar datos de autenticación sin hacer una consulta

El siguiente ejemplo muestra cómo usar el método `SatScraper::confirmSessionIsAlive` para verificar que
los datos de sesión sean (o continuen siendo) correctos. El funcionamiento interno del scraper es:
Si la sesión no se inicializó previamente entonces se intentará hacer el proceso de autenticación,
además se verificará que la sesión (`cookie`) se encuentre vigente.

Se hacen los dos pasos para evitar consumir el servicio de resolución de captcha en forma innecesaria.

```php
<?php declare(strict_types=1);

use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionManager;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;

/** @var CaptchaResolverInterface $captchaResolver */
$satScraper = new SatScraper(CiecSessionManager::create('rfc', 'ciec', $captchaResolver));
try {
    $satScraper->confirmSessionIsAlive();
} catch (LoginException $exception) {
    echo 'ERROR: ', $exception->getMessage(), PHP_EOL;
    return;
}
```

## Ejemplo de autenticación con FIEL

El siguiente ejemplo utiliza una FIEL donde los archivos de certificado y llave privada están cargados
en memoria y se encuentran vigentes. Puede obtener más información de cómo formar la credencial en
el proyecto [`phpcfdi/credentials`](https://github.com/phpcfdi/credentials).

```php
<?php declare(strict_types=1);

use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Fiel\FielSessionManager;
use PhpCfdi\CfdiSatScraper\Sessions\Fiel\FielSessionData;
use PhpCfdi\Credentials\Credential;

/**
 * @var string $certificate Contenido del certificado
 * @var string $privateKey Contenido de la llave privada
 * @var string $passPhrase Contraseña de la llave privada
 */

// crear la credencial
$credential = Credential::create($certificate, $privateKey, $passPhrase);

// crear el objeto scraper usando la FIEL
$satScraper = new SatScraper(FielSessionManager::create($credential));
```

## Quitar la verificación de certificados del SAT

En caso de que los certificados del SAT usados en HTTPS fallen, podría desactivar la verificación de los mismos.
Esto se puede lograr creando el cliente de Guzzle con la negación de la opción `verify`.

No es una práctica recomendada, pero tal vez necesaria ante los problemas a los que el SAT se ve expuesto.
Considera que esto podría facilitar significativamente un ataque (*man in the middle*)
que provoque que la pérdida de su clave CIEC.

**Nota: No recomendamos esta práctica, solamente la exponemos por las constantes fallas que presenta el SAT.**

```php
<?php declare(strict_types=1);
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;

$insecureClient = new Client([
    RequestOptions::VERIFY => false
]);
$gateway = new SatHttpGateway($insecureClient);

/** @var SessionManager $sessionManager */
$scraper = new SatScraper($sessionManager, $gateway);
```

## Compatibilidad

Esta librería se mantendrá compatible con al menos la versión con
[soporte activo de PHP](https://www.php.net/supported-versions.php) más reciente.

También utilizamos [Versionado Semántico 2.0.0](docs/SEMVER.md) por lo que puedes usar esta librería
sin temor a romper tu aplicación.

Consulta la [guía de actualización de la versión `2.x` a la versión `3.x`](docs/UPGRADE-2-3.md).

## Contribuciones

Las contribuciones con bienvenidas. Por favor lee [CONTRIBUTING][] para más detalles
y recuerda revisar el archivo de tareas pendientes [TODO][] y el archivo [CHANGELOG][].

Documentación de desarrollo:

- [Guía de contribuciones](CONTRIBUTING.md)
- [Entorno de desarrollo](develop/EntornoDesarrollo.md)
- [Test de integración](develop/TestIntegracion.md)

## Copyright and License

The `phpcfdi/cfdi-sat-scraper` library is copyright © [PhpCfdi](https://www.phpcfdi.com)
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for more information.

[contributing]: https://github.com/phpcfdi/cfdi-sat-scraper/blob/main/CONTRIBUTING.md
[changelog]: https://github.com/phpcfdi/cfdi-sat-scraper/blob/main/docs/CHANGELOG.md
[todo]: https://github.com/phpcfdi/cfdi-sat-scraper/blob/main/docs/TODO.md

[source]: https://github.com/phpcfdi/cfdi-sat-scraper
[php-version]: https://packagist.org/packages/phpcfdi/cfdi-sat-scraper
[discord]: https://discord.gg/aFGYXvX
[release]: https://github.com/phpcfdi/cfdi-sat-scraper/releases
[license]: https://github.com/phpcfdi/cfdi-sat-scraper/blob/main/LICENSE
[build]: https://github.com/phpcfdi/cfdi-sat-scraper/actions/workflows/build.yml?query=branch:main
[reliability]:https://sonarcloud.io/component_measures?id=phpcfdi_cfdi-sat-scraper&metric=Reliability
[maintainability]: https://sonarcloud.io/component_measures?id=phpcfdi_cfdi-sat-scraper&metric=Maintainability
[coverage]: https://sonarcloud.io/component_measures?id=phpcfdi_cfdi-sat-scraper&metric=Coverage
[violations]: https://sonarcloud.io/project/issues?id=phpcfdi_cfdi-sat-scraper&resolved=false
[downloads]: https://packagist.org/packages/phpcfdi/cfdi-sat-scraper

[badge-source]: https://img.shields.io/badge/source-phpcfdi/cfdi--sat--scraper-blue?logo=github
[badge-discord]: https://img.shields.io/discord/459860554090283019?logo=discord
[badge-php-version]: https://img.shields.io/packagist/php-v/phpcfdi/cfdi-sat-scraper?logo=php
[badge-release]: https://img.shields.io/github/release/phpcfdi/cfdi-sat-scraper?logo=git
[badge-license]: https://img.shields.io/github/license/phpcfdi/cfdi-sat-scraper?logo=open-source-initiative
[badge-build]: https://img.shields.io/github/workflow/status/phpcfdi/cfdi-sat-scraper/build/main?style=flat-square
[badge-reliability]: https://sonarcloud.io/api/project_badges/measure?project=phpcfdi_cfdi-sat-scraper&metric=reliability_rating
[badge-maintainability]: https://sonarcloud.io/api/project_badges/measure?project=phpcfdi_cfdi-sat-scraper&metric=sqale_rating
[badge-coverage]: https://img.shields.io/sonar/coverage/phpcfdi_cfdi-sat-scraper/main?logo=sonarcloud&server=https%3A%2F%2Fsonarcloud.io
[badge-violations]: https://img.shields.io/sonar/violations/phpcfdi_cfdi-sat-scraper/main?format=long&logo=sonarcloud&server=https%3A%2F%2Fsonarcloud.io
[badge-downloads]: https://img.shields.io/packagist/dt/phpcfdi/cfdi-sat-scraper?logo=packagist
