# phpcfdi/cfdi-sat-scraper CHANGELOG

## Acerca de los números de versiones

Respetamos el estándar [Versionado Semántico 2.0.0](https://semver.org/lang/es/).

En resumen, [SemVer](https://semver.org/) es un sistema de versiones de tres componentes `X.Y.Z`
que nombraremos así: ` Breaking . Feature . Fix `, donde:

- `Breaking`: Rompe la compatibilidad de código con versiones anteriores.
- `Feature`: Agrega una nueva característica que es compatible con lo anterior.
- `Fix`: Incluye algún cambio (generalmente correcciones) que no agregan nueva funcionalidad.

**Importante:** Las reglas de SEMVER no aplican si estás usando una rama (por ejemplo `master-dev`)
o estás usando una versión cero (por ejemplo `0.18.4`).

## UNRELEASED 2020-02-14

Estos son algunos de los cambios más importantes relacionados con la compatibilidad si está usando una versión previa.

- Se crea el `SatHttpGateway` que encierra las comunicaciones con el SAT, es éste el que utiliza
  el cliente de Guzzle (`GuzzleInterface`).
- Se cambió el constructor del `SatScraper`, ahora el tercer parámetro es el resolvedor de captchas
  y el cuarto parámetro es un `SatHttpGateway` y es opcional (por si la cookie es solo de memoria).
- Se cambia el objeto `DownloadXml`, antes funcionaba con un callable, ahora funciona con una interfaz
  de tipo `DownloadXmlHandlerInterface` para forzar las firmas de los métodos. También devuelve al
  hacer `download` o `saveTo` el listado de UUID descargados.
- Se cambió el inicio de sesión en el SAT después de revisar el funcionamiento actual, ahora es más limpio
  y con menos llamadas.
- Los filtros solamente llenan los input que deberían llenar.
- El `DownloadTypesOption` ya no es un filtro, pero sigue siendo un `Enum`
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
