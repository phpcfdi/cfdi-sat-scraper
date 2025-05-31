# phpcfdi/cfdi-sat-scraper CHANGELOG

## Acerca de los números de versiones

Usamos [Versionado Semántico 2.0.0](SEMVER.md) por lo que puedes usar esta librería sin temor a romper tu aplicación.

## Cambios aún no liberados en una versión

Ninguno.

## Versión 4.0.0 2025-05-30

Esta versión libera un cambio mayor provocado por el cambio de columnas que implementó el SAT.

En la información de *Metadata* se ajusta la información a la nueva estructura de datos del SAT:

- Ya no existe `fechaProcesoCancelacion`. 
- Se agrega `fechaSolicitudCancelacion`. 
- Se agrega `fechaDeCancelacion`.

Gracias totales a `@cruzcraul` por notar este cambio y la implementación.

Adicionalmente, se hicieron los siguientes cambios por tratarse de una versión mayor:

- Se renombra el parámetro del constructor de `Scraper` de `$maximumRecordsHandler` a `$metadataMessageHandler`.
- Se elimina el método `SatHttpGateway::postLoginData()`, sustituido por `SatHttpGateway::postCiecLoginData()`.
- Se elimina la interfaz `MaximumRecordsHandler`, sustituida por `MetadataMessageHandler`.

Adicionalmente, se hicieron estos cambios al entorno de desarrollo:
 
- Se actualizan las herramientas de desarrollo.

## Versión 3.3.3 2024-10-03

- Se agrega la documentación principal para usar el resolvedor de captchas
  [`phpcfdi/image-captcha-resolver-boxfactura-ai`](https://github.com/phpcfdi/image-captcha-resolver-boxfactura-ai).
- Se agrega la documentación de `docs/EjemploConsumo.md` para usar el resolvedor de captchas `BoxFacturaAIResolver`,
  con todos los pasos para hacer correr el ejemplo.
- En el archivo `composer.json` se recomienda `phpcfdi/image-captcha-resolver-boxfactura-ai`.

Los siguientes cambios aplican para el entorno de desarrollo:

- Se modifica el archivo `composer.json` para:
  - Requiere `phpcfdi/image-captcha-resolver-boxfactura-ai`.
  - Utiliza `phpcfdi/image-captcha-resolver-boxfactura-ai` para PHP 8.1 en adelante.
  - Desinstala `phpcfdi/image-captcha-resolver-boxfactura-ai` para menores de PHP 8.1.
- Se actualiza la documentación de `develop/TestIntegracion.md` donde se remueve `eclipxe/captcha-local-resolver`
  y se menciona `phpcfdi/image-captcha-resolver-boxfactura-ai`.
- Se cambian las pruebas de integración para usar `phpcfdi/image-captcha-resolver-boxfactura-ai`.
- Se actualizan las herramientas de desarrollo.

Esta actualización únicamente se ha podido hacer gracias al trabajo de investigación y entrenamiento de un modelo Onnx
de inteligencia artificial de nuestros amigos de [BOX Factura](https://www.boxfactura.com/).
Su trabajo en el repositorio [`BoxFactura/sat-captcha-ai-model`](https://github.com/BoxFactura/sat-captcha-ai-model)
permitió crear el resolvedor `BoxFacturaAIResolver`, pero, sobre todo, simplificar la resolución de captchas,
tanto en forma local como en producción.
**Muchas gracias**.

## Versión 3.3.2 2024-09-09

- PHPStan encontró una comparación superflua que fue eliminada para corregir el proceso de integración continua.
- Se agregan comentarios a clases *Null* para mejorar la mantenibilidad.
- Se actualiza el año del archivo de licencia a 2024.
- Se corrige la variable `php-versions` por `php-version` en el flujo de trabajo `tests`.
- Se actualizan las herramientas de desarrollo.

## Versión 3.3.1 2024-05-22

- PHPStan encontró un problema en una especificación de tipo en un método de prueba,
  se ha corregido solo para que el proceso de integración continua no falle.
- Se actualizan las dependencias de los componentes de Symfony para soportar la versión 7.
- Se actualizan los flujos de trabajo de GitHub para usar las acciones versión 4.
- Se usa `php-version` en singular, en lugar de `php-versions`.
- Se actualizan las herramientas de desarrollo.

## Versión 3.3.0 2023-12-03

Se agregó la interfaz `MetadataMessageHandler` que permite recibir notificaciones de la descarga de *Metadata*.
Dentro de las notificaciones se incluye la que ocurre cuando se encontraron 500 registros en un solo segundo.

Se deprecó la interfaz `MaximumRecordsHandler`, es sustituida por `MetadataMessageHandler`.

Se deprecó el método `SatScraper::getMaximumRecordsHandler()` a favor de `SatScraper::getMetadataMessageHandler()`.

Para no introducir un cambio que rompa la compatibilidad, el constructor de `SatScraper` sigue soportando la
creación del objeto con el argumento `MaximumRecordsHandler $maximumRecordsHandler`.
En su lugar, debería enviar un objeto que implemente la interfaz `MetadataMessageHandler`.

Se introduce el objeto `NullMetadataMessageHandler` que implementa la interfaz `MetadataMessageHandler`, 
pero no realiza ninguna acción en sus métodos.

Otros cambios al entorno de desarrollo:

- Se actualizan las dependencias de desarrollo.
- Se agrega PHP 8.3 a la matrix de pruebas.
- Los trabajos se ejecutan con PHP 8.3.
- Para `php-cs-fixer` se sustituye `function_typehint_space` con `type_declaration_spaces`.

## Versión 3.2.5 2023-07-03

Algunos métodos intentaban atrapar una excepción `RuntimeException` proveniente de `Crawler`, sin embargo, 
la excepción no era correcta, se atrapa ahora `Throwable`. Gracias a PHPStan por detectar el problema.

Se actualizan las dependencias de desarrollo.

## Versión 3.2.4 2023-06-22

Se corrige el mensaje relacionado con el envío de datos incorrectos al iniciar sesión usando CIEC.

Se corrige la dependencia de `CaptchaImage` por `CaptchaImageInterface` en `CiecLoginException`.

Se extrae la lógica para hacer la petición de acceso vía CIEC a un método separado.
En una prueba de concepto esto ayuda a crear la sesión usando un valor conocido de *Captcha*.

Se agregan los siguientes cambios en el entorno de desarrollo:

- Se corrige la liga del proyecto en el archivo `CONTRIBUTING.md`.
- Se actualizan las herramientas de desarrollo.
- Se agrega la herramienta `composer-normalize`.
- En el flujo de trabajo de cobertura de código se ejecuta usando PHP 8.2.
- Se elimina `PHP_CS_FIXER_IGNORE_ENV` del flujo de trabajo principal en el trabajo `php-cs-fixer`.
- Se agrega la opción para ejecutar flujos de trabajo a solicitud.

### Cambios no liberados: 2023-02-13

- Se corrige la configuración de `sonar-project.properties` para excluir correctamente los archivos para pruebas.
- Se excluye correctamente el archivo `sonar-project.properties` del paquete de Git.

## Versión 3.2.3 2023-05-25

- Se actualiza la dependencia de `guzzlehttp/promises` a versión mínima 2.0.
- Se actualiza la dependencia de `psr/http-message` a versiones mínimas 1.1 o 2.0.
- Se actualiza la dependencia de `phpcfdi/image-captcha-resolver` a versión mínima 0.2.3.

Los siguientes cambios aplican al entorno de desarrollo:

- La ejecución de `php-cs-fixer` dentro de `composer` se condiciona a mínimo PHP 8.0.
- Se refactoriza la clase `RepositoryItem` para que las responsabilidades de la creación de una instancia 
  a partir de un arreglo se realizen en la clase `RepositoryItemFactory`.
- Se corrigen las pruebas para usar `psr/http-message:^2.0`.
- Se corrige el issue falso positivo encontrado por PHPStan al convertir un objeto a cadena de caracteres.
- Actualización de herramientas de desarrollo.

También se concluyen los siguientes cambios previos no liberados.

### Cambios no liberados: 2023-02-13

- Actualización de herramientas de desarrollo.
- Se agrega la configuración en `composer.json` para no permitir el uso de *plugins* de `php-http/discovery`.
- En las pruebas, se refactoriza `SatHttpGatewayTest::testMethodPostLoginDataIsDeprecated` para probar que
  el método `postLoginData` está deprecado, dado que PHPUnit 9.6 descontinuó el método `expectDeprecation`.

### Cambios no liberados: 2023-01-31

- Actualización de herramientas de desarrollo.
- En las pruebas, se elimina una anotación para PHPStan para ignorar un error al realizar `unset` sobre una
  variable indefinida en un objeto de tipo `Metadata`.

## Versión 3.2.2

### Regresar *Motivo de cancelación* y *Folio de sustitución*

Se regresa la lectura de *Motivo de cancelación* (`motivoCancelacion`) y *Folio de sustitución* (`folioSustitucion`).
Aparentemente, en la fecha 2023-01-12 el SAT ha regresado estas columnas.

## Versión 3.2.1

### Quitar *Motivo de cancelación* y *Folio de sustitución*

Se elimina la lectura de *Motivo de cancelación* (`motivoCancelacion`) y *Folio de sustitución* (`folioSustitucion`).
Aparentemente, en la fecha 2023-01-04 el SAT ha eliminado estas columnas.

### Otros cambios menores

- Actualización de licencia a 2023. ¡Feliz año!.
- Actualización de flujos de trabajo sustituyendo la directiva `::set-output` con `$GITHUB_OUTPUT`.
- Corrección de la insignia del flujo de construcción `build`.

### Cambios previos

#### 2022-11-09: Corrección de construcción de integración continua

- Se actualizaron las herramientas de desarrollo.
- Se agrega PHP 8.2 a la matriz de pruebas en el proceso de integración continua.
- Se corrige la firma (`phpdoc`) del método `HttpLogger::bodyToVars`.
- Se corrige el método `Repository::randomize` pues perdía las llaves del arreglo.
- Se corrige el archivo de configuración de `php-cs-fixer` porque la regla `no_trailing_comma_in_singleline_array` está deprecada.

#### 2022-10-22: Corrección de construcción de integración continua

- Se actualizaron las herramientas de desarrollo.
- Se aplicó la corrección de `php-cs-fixer`.
- Se corrigió el nombre de usuario de `@git-micotito` en este mismo archivo.

## Versión 3.2.0

### Agregar *Motivo de cancelación* y *Folio de sustitución*

Se agrega la lectura de *Motivo de cancelación* (`motivoCancelacion`) y *Folio de sustitución* (`folioSustitucion`) a `Metadata`. Así como la extracción de estos datos en `MetadataExtractor`.
Gracias `@TheSpectroMx`.

## Versión 3.1.2

### Filtrado de recursos incorrecto

Problema: Si el objeto `Metadata` contenía la entrada del recurso, pero estaba vacía,
entonces la función `hasResource` devolvía verdadero. Esto hacía que fallara el filtrado.
Se corrigió el problema comparando contra el valor vacío y no contra la existencia de la llave.
Gracias `@git-micotito` por la detección del problema.

### Actualización de `eclipxe/micro-catalog`

La nueva versión de `eclipxe/micro-catalog` necesita la especificación del tipo de datos
para `MicroCatalog` en la clase `ComplementsOption`.

### Actualización de herramientas de desarrollo

- Se actualizan las herramientas.
- Se elimina la regla `method_argument_space` para dejar la definición por defecto de PSR-12.

## Versión 3.1.1

### Cambios en el código

Se admite la compatibilidad con Symfony 6. Esto evita que se tengan que degradar componentes a la versión 5.

Se depreca `SatHttpGatewayException::postLoginData` para crear el método específico
`SatHttpGatewayException::postCiecLoginData`. Esto no altera la funcionalidad actual.

Se agrega la dependencia faltante `mbstring`.

### Cambios en el entorno de desarrollo

Se mejoran los test para probar valores idénticos en lugar de valores iguales.

Se actualizan las herramientas de desarrollo.

Se actualiza el archivo de configuración de `php-cs-fixer`. 

## Versión 3.1.0

### Agregar *RFC a cuenta de terceros*

Se agrega el filtro `RfcOnBehalfOption`.
Se agrega la lectura de esta información en `Metadata` como `rfcACuentaTerceros`.
Se agrega la documentación para filtrar y leer el campo de *RFC a cuenta de terceros*.

### Acceso por propiedades a `Metadata`.

Se documentan las propiedades en `Metadata` para acceder a ellas usando, por ejemplo `$metadata->uuid`.

### Documentación 2022-03-01

Se agrega la documentación para configurar el cliente de cURL con `DEFAULT@SECLEVEL=1`.

Las pruebas se corren con `DEFAULT@SECLEVEL=1`.

Se agrega el código que ejemplifica cómo validar que la FIEL no es un CSD y que es válido al momento de la consulta.

### Entorno de desarrollo 2022-03-01

Al ejecutar el flujo de integración continua, se usan los path en el archivo `phpcs.xml.dist`.

## Versión 3.0.0

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

## Versión 2.1.1

Se corrige un bug al consumir el servicio de Anti-Captcha donde estaba asumiendo que el código de error
era un string vacío cuando en realidad es un número entero.

- 2021-07-05: Tests: En las pruebas de `AntiCaptchaTinyClient` las respuestas preparadas no tenían correctamente
  formados los `HEADERS`.

- 2021-07-05: CI: Se permite que falle la subida del archivo de cobertura de código a Scrutinizer-CI.

## Versión 2.1.0

Se agrega la implementación para resolver el *captcha* en la clase `AntiCaptchaResolver`,
que a su vez usa la clase `AntiCaptchaTinyClient` como un cliente de conectividad mínimo.

Se modifica el entorno de desarrollo y bloques de documentación de PHP para asegurar la construcción del proyecto.
Estos cambios no son importantes si estás usando la librería y son con respecto a desarrollo interno.

Los flujos de pruebas de integración contínua ahora se migraron a GitHub Actions,
Travis-CI ha sido de gran ayuda en el desarrollo de este proyecto.

## Versión 2.0.0

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

## Versión 1.0.1

- Se actualizan dependencias:
    - `symfony/dom-crawler` de `^4.2|^5.0` a `5.1`.
    - `symfony/css-selector` de `^4.2|^5.0` a `5.1`.
    - `guzzlehttp/guzzle` de `^6.3` a `7.0`.
- Se corrigen las descripciones de las clases `DownloadType`, `ComplementsOption`, `RfcOption`, `StatesVoucherOption`
  y `UuidOption`.
- Se agregó una sección en el README *Verificar datos de autenticación sin hacer una consulta* (issue #35).
- Se cambia en desarrollo la inicialización de `Dotenv` porque se deprecó la forma anterior en `symfony/dotenv: ^5.1`.
- Se cambia en desarrollo la dependencia de `symfony/dotenv` de `^4.2|^5.0` a `^5.1`.

## Versión 1.0.0

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
