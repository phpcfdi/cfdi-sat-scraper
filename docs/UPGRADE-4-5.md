# Guía de actualización de `4.x` a `5.x`

Los cambios en esta versión son principalmente de actualización de lenguaje y especificación de tipos de datos.
Si actualmente estás en la versión 4 y PHP mayor o igual a 8.2, entonces no necesitarás hacer ningún cambio.

## Versión mínima de PHP

La versión mínima de PHP es ahora 8.2.
A la fecha de liberación 2025-09-13, PHP 8.2 no tiene soporte activo y su soporte de seguridad termina en 2026-12-31.
Mantener versiones anteriores de PHP es costoso y no permite obtener las mejoras asociadas al lenguaje.

## Versión PHP 8.4 soportada

Se comprobó la compatibilidad con PHP 8.4.

## Cambios en la interfaz `ResourceDownloaderPromiseHandlerInterface`

La interfaz `ResourceDownloaderPromiseHandlerInterface` en los métodos `promiseFulfilled` y `promiseRejected` 
devolvía `null`, ahora estos métodos están declarados como `void` (sin valor de retorno).

## Tipos de datos

Se agregaron los tipos de datos específicos y `mixed`.
Es importante si está **extendiendo** alguna de estas clases:

- `PhpCfdi\CfdiSatScraper\Contracts\FilterOption\UuidOption`.
- `PhpCfdi\CfdiSatScraper\Contracts\FilterOption\RfcOnBehalfOption`.
- `PhpCfdi\CfdiSatScraper\Contracts\FilterOption\RfcOption`.
- `PhpCfdi\CfdiSatScraper\Contracts\FilterOption\ComplementsOption`.
- `PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadError`.
- `PhpCfdi\CfdiSatScraper\ResourceDownloader`.
