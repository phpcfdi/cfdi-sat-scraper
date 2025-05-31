# Guía de actualización de `3.x` a `4.x`

## Nuevos valores de `Metadata`

El día 2025-05-30 el SAT implementó un cambio en su estructura de datos, lo que llevó a que ahora no se leyera
correctamente la *Fecha de Proceso de Cancelación* (`fechaProcesoCancelacion`).

El SAT en su lugar agregó dos campos:

- *Fecha de Solicitud de Cancelación* (`fechaSolicitudCancelacion`).
- *Fecha de Cancelación* (`fechaDeCancelacion`).

## Cambio en el constructor de `Scraper`

El constructor de `Scraper` anteriormente recibía el parámetro `$maximumRecordsHandler`
que ahora se ha renombrado a `$metadataMessageHandler`.

## Se elimina `MaximumRecordsHandler`

La interfaz `MaximumRecordsHandler` se había deprecado desde la versión 3.3.0.
En su lugar se debe usar `MetadataMessageHandler`.

## Se elimina `SatHttpGateway::postLoginData()`

El método `SatHttpGateway::postLoginData()` se había deprecado desde la versión 3.1.1.
En su lugar se debe usar  `SatHttpGateway::postCiecLoginData()`.
