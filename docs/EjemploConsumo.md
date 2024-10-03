# Ejemplo de consumo

Este ejemplo está documentado con las siguientes consideraciones:

- El RFC está en el entorno en la variable `SAT_AUTH_RFC`
- La clave CIEC está en el entorno en la variable `SAT_AUTH_CIEC`
- Desde donde se está llamando al código existen las carpetas `build/cookies/` y `build/cfdis/`
- Se está usando el objeto `BoxFacturaAIResolver`, así que se espera que el captcha se resuelva automáticamente.
- El modelo de resolución de captcha está en la carpeta `storage/boxfactura-model`.

Y se espera que:

- Se pueda reutilizar la `cookie` si no ha expirado y así no tener que volver a resolver un captcha.
- Se carge una lista de CFDI recibidos y vigentes entre 2019-12-01 y 2019-12-31.
- Ocurra la descarga de los XML correspondientes a dichos registros.

La rutina de descarga intentará hasta que haya descargado todos los archivos.

## Instalación de dependencias

### Instalación de los paquetes

Paquetes base:

```shell
composer require phpcfdi/cfdi-sat-scraper phpcfdi/image-captcha-resolver-boxfactura-ai
```

Instalación de la librería `libonnxruntime.so` para poder interpretar modelos Onnx:

```shell
composer run-script post-update-cmd -d vendor/ankane/onnxruntime/
```

Instalación del modelo Onnx para resolver el captcha:

```shell
bash vendor/phpcfdi/image-captcha-resolver-boxfactura-ai/bin/download-model storage/boxfactura-model
```

## Ejemplo de ejecución

Archivo `demo-ciec.php`:

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionManager;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\BoxFacturaAIResolver;

require __DIR__ . '/vendor/autoload.php';

$rfc = strval($_SERVER['SAT_AUTH_RFC'] ?? '');
$claveCiec = strval($_SERVER['SAT_AUTH_CIEC'] ?? '');
$cookieJarPath = sprintf('%s/build/cookies/%s.json', getcwd(), $rfc);
$downloadsPath = sprintf('%s/build/cfdis/%s', getcwd(), $rfc);

$client = new Client([
    'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'],
]);
$gateway = new SatHttpGateway($client, new FileCookieJar($cookieJarPath, true));

$configsFile = __DIR__ . '/storage/boxfactura-model/configs.yaml';
$captchaResolver = BoxFacturaAIResolver::createFromConfigs($configsFile);

$ciecSessionManager = CiecSessionManager::create($rfc, $claveCiec, $captchaResolver);

$satScraper = new SatScraper($ciecSessionManager, $gateway);

$resourceDownloader = $satScraper->resourceDownloader(ResourceType::xml())
    ->setConcurrency(20);

$query = new QueryByFilters(new DateTimeImmutable('2019-12-01'), new DateTimeImmutable('2019-12-31'));
$query->setDownloadType(DownloadType::recibidos()) // default: emitidos
    ->setStateVoucher(StatesVoucherOption::vigentes());   // default: todos

$list = $satScraper->listByPeriod($query);
printf("\nSe encontraron %d registros", $list->count());

$list = $list->filterWithResourceLink(ResourceType::xml());
printf("\nPero solamente %d registros contienen archivos XML", $list->count());

while ($list->count() > 0) {
    // perform download
    printf("\nIntentando descargar %d archivos: ", $list->count());
    $downloadedUuids = $resourceDownloader->setMetadataList($list)
        ->saveTo($downloadsPath, true);
    printf('%d descargados.', count($downloadedUuids));

    // check that at least one uuid were downloaded
    if ([] === $downloadedUuids) {
        printf("\nNo se pudieron descargar %d registros", $list->count());
        break; // exit loop since no records were downloaded
    }
    
    // reduce list
    $list = $list->filterWithOutUuids($downloadedUuids);
}
```

Creación de los directorios necesarios en el script:

```shell
mkdir -p build/cookies build/cfdis
```

Ejecución de `demo-ciec.php`:

```shell
env SAT_AUTH_RFC="COSC8001137NA" SAT_AUTH_CIEC="******" php demo-ciec.php
```

Que responde:

```text
Se encontraron 385 registros
Pero solamente 385 registros contienen archivos XML
Intentando descargar 385 archivos: 385 descargados.
```
