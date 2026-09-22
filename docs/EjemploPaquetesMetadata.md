# Ejemplo de descarga de paquetes de Metadata

En este ejemplo se muestra cómo se puede utilizar el scraper de paquetes de metadata `MetadataPackageScraper`.

Es importante recordar que:

Los paquetes de tipo *Recibidos* se generan por 1 día o por 1 mes.
La consulta de 2026-01-01 00:00:00 a 2026-01-31 23:59:58 (segundo 58 y no 59) generará 31 paquetes.
Los paquetes de tipo *Emitidos* se generan por un periodo.

Los paquetes pueden tardar en generarse hasta 48 horas. El código ostrado aquí es solo un ejemplo,
al momento de implementarlo es necesario persistir la lista de paquetes generados, para
posteriormente verificar si ya se encuentrasn disponibles, y entonces descargarlos.

Los paquetes duran 3 días disponibles para descarga, después de ese tiempo son eliminados.

## Ejemplo para solicitar varios periodos

En este ejemplo se usa la autenticación por FIEL, para descargar paquetes de Metadata de CFDI recibidos
de enero a septiembre 2026. Esto generará 9 paquetes de datos.

Los pasos importantes son:

1. Generar la consulta `$query`.
2. Enviar la consulta pata obtener los paquetes `$results`.
3. Obtener los paquetes disponibles (puede tardar hasta 48 horas en generarse) `$available`.
4. Solo usar los paquetes que sí se pudieron generar `$packages`.
5. Descargar cada uno de los paquetes generados `$packages` si es que están disponibles `$available`.

```php
<?php declare(strict_types=1);

use GuzzleHttp\Client;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\MetadataPackageScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Fiel\FielSessionManager;
use PhpCfdi\Credentials\Credential;

/** @var Credential $credential */

$gateway = new SatHttpGateway(
    new Client(['curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1']])
);
$sessionManager = FielSessionManager::create($credential);
$scraper = new MetadataPackageScraper($sessionManager, $gateway);

$query = new QueryByFilters(
    new DateTimeImmutable('2026-01-01'),
    new DateTimeImmutable('2026-09-01'), // se usará finalmente 2026-09-31 23:59:59
    DownloadType::recibidos()
);

// requestByMonths pone la el inicio al primer día del mes a las 00:00:00
// y el final al último día del mes a las 23:59:59 
$results = $scraper->requestByMonths($query);
echo 'Paquetes generados:', PHP_EOL;
foreach ($results as $result) {
    echo '  ', $result->getStartDate()->format('Y-m-d H:i:s'),
        ' ', $result->getEndDate()->format('Y-m-d H:i:s'),
        ' ', $result->hasUuid() ? $result->getUuid() : 'Sin resultados',
        PHP_EOL;
}

$available = $scraper->listAvailablePackages();
echo 'Paquetes disponibles:', PHP_EOL;
foreach ($available as $package) {
    echo '  ', $package->getUuid(), PHP_EOL;
}

// ahora la lista de paquetes solo tiene los que sí tienen UUID
$packages = $results->filterWithUuid();
foreach ($packages as $package) {
    $packageUuid = $package->getUuid();
    
    // no intentar descargar el paquete si ya sabemos que no está disponible
    if (! $available->has($packageUuid)) {
        continue;
    }
    
    echo 'Descargando ', $packageUuid, ': ';
    $destinationPath = sprintf('packages/%s.zip', strtolower($packageUuid));
    try {
        $scraper->downloadPackageFromAvailable($available, $packageUuid, $destinationPath);
    } catch (Throwable $exception) {
        echo $exception->getMessage(), PHP_EOL;
        continue;
    }
    echo $destinationPath, PHP_EOL;
}
```
