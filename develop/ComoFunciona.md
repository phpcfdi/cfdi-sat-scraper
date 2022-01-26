# Cómo funciona

El punto de entrada es la clase `SatScraper`.

Al `SatScraper` se le pide que ejecute consultas, de las que hay dos definiciones: `QueryByFilters` y `QueryByUuid`.

La propiedad que comparten todas las consultas es `DownloadType`, que define si se trata de *recibidos* o *emitidos*.

Tanto en *recibidos* o *emitidos* se puede consultar por *UUID* o por *filtros*. Por lo tanto hay 4 tipos de consultas:
Recibidos por UUID, Recibidos por filtros, Emitidos por UUID y Emitidos por filtros.
Sin embargo, se simplifica porque el portal del SAT funciona casi igual para recibidos y emitidos, por lo que se puede
reducir a 3 tipos: Por UUID (recibidos/emitidos), Recibidos por filtro, Emitidos por filtro.

Cuando se le pide a `SatScraper` listar, todos los UUID son de un mismo origen: todos emitidos o todos recibidos.
Al invocar a `listByUuids`, para cada UUID se genera una consulta `QueryByUuid`.
Los otros métodos esperan recibir una consulta por filtros `QueryByFilters`.

Para poder generar las descargas se utiliza un `MetadataDownloader`, el cual tiene métodos específicos para trabajar
con la consulta. Si la consulta es por varios UUID se realiza sobre los uuid únicos. Si la consulta es por filtros
se subdivide en consultas por día.

Para ejecutar la consulta se usa el objeto `QueryResolver` recibiendo un objeto `InputsInterface`.
El objeto `InputsInterface` es un encapsulador de la consulta, que contiene la consulta y los métodos necesarios
para crear los inputs (datos de las llamadas HTTP POST) basados en la consulta. El objeto `MetaDownloader` es el
encargado de definir cuál es la implementación de `InputsInterface` que utilizará: `InputsByUuid`,
`InputsByFiltersReceived` y `InputsByFiltersIssued`.

```
- interface InputsInterface
    - abstract InputsGeneric
        - concrete InputsByUuid
        - abstract InputsByFilters
            - concrete InputsByFiltersReceived
            - concrete InputsByFiltersIssued
```

La resolución de una consulta de `QueryResolver` consta de 3 pasos:

1. Entrar a la página principal según el tipo de consulta (recibidos o emitidos) y obtener todos los inputs.
1. Hacer la selección del tipo de consulta (por UUID o por filtros) y recapturar los inputs que hubieran cambiado.
1. Hacer la consulta con los datos específicos (uuid, fechas, rfc, complemento, estado, etc.)
1. Generar el objeto `MetadataList` a partir de los datos devueltos.

Por lo anterior, los pasos son:

```
SatScraper -> MetadataDownloader -> QueryResolver
          Query          Query[] -> Inputs
```

