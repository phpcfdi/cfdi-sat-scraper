# phpcfdi/cfdi-sat-scraper CHANGELOG

## Acerca de los números de versiones

Usamos [Versionado Semántico 2.0.0](SEMVER.md) por lo que puedes usar esta librería sin temor a romper tu aplicación.

## Cambios aún no liberados en una versión.

## Version 3.0.0

Vea la [Guía de actualización de `2.x` a `3.x`](UPGRADE-2-3.md).

Este es el listado de cambios más relevantes:

- A partir de esta versión se puede realizar la autenticación del cliente utilizando FIEL.
- El método `SatScraper::registerOnPortalMainPage` fue renombrado a `SatScraper::accessPortalMainPage`.
- Se cambió la extracción y resolución de captchas a la librería 
  [`phpcfdi/image-captcha-resolver`](https://github.com/phpcfdi/image-captcha-resolver).
- Se cambió el manejador de máximo de registros de una función *callable* `callable(DateTimeImmutable): void`
  a una interfaz `MaximumRecordsHandler`.
- Se eliminan las extensiones que estaban requeridas, pero no están más en uso: `libxml`, `simplexml` y `filter`.
- Se actualiza toda la documentación del proyecto.

Cambios relevantes en desarrollo:

- Se cambia de `development/install-development-tools` a `phive`.
- Se mejoraron los bloques `phpdoc`.
- El proyecto se ha integrado con SonarCloud y se están utilizando sus métricas:
  <https://sonarcloud.io/project/overview?id=phpcfdi_cfdi-sat-scraper>.
- Se deja de usar la integración con Scrutinizer CI. Gracias Srutinizer.

## Version 2.1.1

Se corrige un bug al consumir el servicio de Anti-Captcha donde estaba asumiendo que el código de error
era un string vacío cuando en realidad es un número entero.

- 2021-07-05: Tests: En las pruebas de `AntiCaptchaTinyClient` las respuestas preparadas no tenían correctamente
  formados los `HEADERS`.

- 2021-07-05: CI: Se permite que falle la subida del archivo de cobertura de código a Scrutinizer-CI.

## Version 2.1.0

Se agrega la implementación para resolver el *captcha* en la clase `AntiCaptchaResolver`,
que a su vez usa la clase `AntiCaptchaTinyClient` como un cliente de conectividad mínimo.

Se modifica el entorno de desarrollo y bloques de documentación de PHP para asegurar la construcción del proyecto.
Estos cambios no son importantes si estás usando la librería y son con respecto a desarrollo interno.

Los flujos de pruebas de integración contínua ahora se migraron a GitHub Actions,
Travis-CI ha sido de gran ayuda en el desarrollo de este proyecto.

## Version 2.0.0

### Descarga de diferentes tipos de recursos

Hasta la versión `1.x` el scraper solo descargaba los archivos de CFDI de tipo XML.
A partir de la versión `2.x` es posible descargar 4 tipos diferentes definidos en el enumerador `ResourceType`:

- El tipo `ResourceType::xml()` es para el archivo XML del CFDI.
- El tipo `ResourceType::pdf()` es para la representación impresa en formato PDF del CFDI.
- El tipo `ResourceType::cancelRequest()` es para la solicitud de cancelación del CFDI en formato PDF.
- El tipo `ResourceType::cancelVoucher()` es para el acuse de cancelación del CFDI en formato PDF.

El método `SatScraper::xmlDownloader` ha cambiado a `SatScraper::resourceDownloader`.

Las clases llamadas `XmlDownload...` ahora se llaman `ResourceDownload...`.
Esto incluye clases de la API, contratos, excepciones, clases internas, etc.

Se actualizó la documentación y ejemplos para la nueva API.

### Cambios desde 2020-10-14

Este cambio no afectó la versión liberada y no requiere de un nuevo release.

- La construcción en Travis-CI se rompió porque PHPStan version 0.12.55 ya entiende las estructuras de control
  de PHPUnit, por lo que sabe que el código subsecuente es código muerto. Se corrigieron las pruebas con problemas.
- Se actualizó la herramienta `develop/install-development-tools`

## Version 1.0.1

- Se actualizan dependencias:
    - `symfony/dom-crawler` de `^4.2|^5.0` a `5.1`.
    - `symfony/css-selector` de `^4.2|^5.0` a `5.1`.
    - `guzzlehttp/guzzle` de `^6.3` a `7.0`.
- Se corrigen las descripciones de las clases `DownloadType`, `ComplementsOption`, `RfcOption`, `StatesVoucherOption`
  y `UuidOption`.
- Se agregó una sección en el README *Verificar datos de autenticación sin hacer una consulta* (issue #35).
- Se cambia en desarrollo la inicialización de `Dotenv` porque se deprecó la forma anterior en `symfony/dotenv: ^5.1`.
- Se cambia en desarrollo la dependencia de `symfony/dotenv` de `^4.2|^5.0` a `^5.1`.

## Version 1.0.0

- Se establece la versión mínima de PHP a 7.3.
- Se revisan las expresiones regulares y `json_encode`/`json_decode` con el paso a 7.3.
- Se cambia la versión de PHPUnit a 9.1.
- Se corrige `linguist-detectable=false` para los archivos en `tests/_files` que estaba mal puesto.

## UNRELEASED 2020-04-12

- El filtro por complemento `ComplementsOption` ya no es un `Enum`, ahora es un `MicroCatalog`. 
  De esta forma se puede tener mucha más información relacionada con el complemento y por ejemplo
  poder ofrecer una lista de opciones de catálogos.
- La modificación de `ComplementsOption` es compatible con la forma de crear los objetos y de comprobar si es
  de un tipo en especial (por ejemplo: `ComplementsOption::todos()` y `ComplementsOption::isTodos()`).

## UNRELEASED 2020-02-23

En este release se cambió totalmente la librería, tanto en el exterior como en el funcionamiento interno.

Los cambios más importantes para los usuarios de la librería son:

- La consulta por filtros ya no usa `Query`, que dejó de existir, ahora se llama `QueryByFilters`.
- Los métodos para obtener el *metadata* han cambiado a `listByUuids`, `listByPeriod` y `listByDateTime`.
- El método para crear el descargador de XML ahora se llama `xmlDownloader` y recibe optionalmente todos los parámetros.
- Si se quiere personalizar el descargador se debe hacer implementando la interfaz `XmlDownloadHandlerInterface`.
- Los datos para poder autenticarse con el SAT se almacenan en un objeto `SatSessionData`.
- Se crea toda una estructura para excepciones:
    - Todas implementan la interfaz `SatException`.
    - Las excepciones usan las SPL de PHP: `RuntimeException`, `InvalidArgumentException` y `LogicException`.
    - Las excepciones lógicas indican que debes tener un error en la forma en que estás usando la aplicación.
    - Las excepciones de tiempo de ejecución es porque algo inesperado ha ocurrido, pero no necesariamente es
      por un error en la implementación.
    - Los problemas relacionados con el proceso de autenticación son `LoginException`.
    - Los problemas relacionados con las transacciones HTTP con el SAT son `SatHttpGatewayException`,
      con dos especializaciones: `SatHttpGatewayClientException` y `SatHttpGatewayResponseException`.
    - Los problemas relacionados con la ejecución de la descarga de XML se son `XmlDownloadError`,
      con dos especializaciones: `XmlDownloadRequestExceptionError` y `XmlDownloadResponseError`.

Los cambios importantes al interior de la librería son:

- La estructura para obtener el listado de metadata ahora pasa por: `SatScraper` crea y ejecuta un `MetadataDownloader`
  que crea y ejecuta un `QueryResolver` que crea y ejecuta un `MetadataExtractor`.
- La generación de los datos que se envían por POST para seleccionar el tipo de consulta y ejecutarla se cambian
  a objetos `Input`. Estos objetos son especializaciones que a partir de la consulta generan los inputs adecuados.

## UNRELEASED 2020-02-14

Estos son algunos de los cambios más importantes relacionados con la compatibilidad si está usando una versión previa.

- Se crea el `SatHttpGateway` que encierra las comunicaciones con el SAT, es este el que utiliza
  el cliente de Guzzle (`GuzzleInterface`).
- Se cambió el constructor del `SatScraper`, ahora el tercer parámetro es el resolvedor de captchas
  y el cuarto parámetro es un `SatHttpGateway` y es opcional (por si la cookie es solo de memoria).
- Se cambia el objeto `DownloadXml`, antes funcionaba con un callable, ahora funciona con una interfaz
  de tipo `DownloadXmlHandlerInterface` para forzar las firmas de los métodos. También devuelve al
  hacer `download` o `saveTo` el listado de UUID descargados.
- Se cambió el inicio de sesión en el SAT después de revisar el funcionamiento actual, ahora es más limpio
  y con menos llamadas.
- Los filtros solamente llenan los input que deberían llenar.
- La clase `DownloadTypesOption` ya no es un filtro, pero sigue siendo un `Enum`.
- Se removieron las constantes `URLS::SAT_HOST_CFDI_AUTH`, `URLS::SAT_HOST_PORTAL_CFDI` y `URLS::SAT_URL_PORTAL_CFDI_CONSULTA`
- Se movieron las clases internas al espacio de nombres interno.

## UNRELEASED 2020-02-10

- Se cambia la interfaz `PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface`, la nueva forma de uso
  sería: `$answer = $resolver->decode($image);`
    - Cambia `function decode(): ?string` a `function decode(string $base64Image): string`.
    - Se elimina `function setImage(string $base64Image): self`

## UNRELEASED 2020-02-09

- Se corrigió un bug que no permitía descargar por UUID
- Se cambió el objeto `\PhpCfdi\CfdiSatScraper\Filters\Options\RfcReceptorOption` a
  `PhpCfdi\CfdiSatScraper\Filters\Options\RfcOption`.
- Se agregaron test de integración con información basada en un *único punto de verdad*. 
  Consulta la guía en `develop/docs/TestIntegracion.md`.
- Se agregó integración contínua con Travis-CI & Scrutinizer.
- Se establece análisis de código a `phpstan level 5`.

## UNRELEASED 2020-01-28

- Se corrigió el problema de descargas del SAT, la librería estaba en un estado no usable.
- Se inició con el proceso de llevar la librería a una versión mayor `1.0.0`.
