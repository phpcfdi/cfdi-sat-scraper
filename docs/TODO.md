# phpcfdi/cfdi-sat-scraper tareas pendientes

## Pendientes

- Core:
    - Clases que vienen de Enum deberían ser finales.
    - Incrementar las pruebas unitarias a un mínimo de 80%.
    - Implementar `phpcfdi/image-captcha-resolver`.
- Documentación:
    - Todos los puntos de entrada deben tener phpdoc.
- Entorno de desarrollo:
    - CodeStyle: Subir a PSR-12.
    - Definir el estilo de código de la coma final en un listado de parámetros.

## Wishlist

- Code coverage 100%.
- Se comentó en el canal de discord que podría servir procurar la integración no solo por clave CIEC,
  y permitir también la autenticación por FIEL.

## Realizadas

- 2020-07-18:
    - Se actualizó `guzzlehttp/guzzle` a `^7.0`.
    - Se actualizó `symfony/dom-crawler` y `symfony/css-selector` a `^5.1`.

- 2020-04-18:
    - Se actualizaron las versiones a `php:>=7.3` y `phpunit:^9.1`.
    - Al fin se libera la versión `1.0` del proyecto.
    - Gracias a [`rector`](https://github.com/rectorphp/rector/) en forma local se analizaron
      los cambios de versiones de `php` y `phpunit`.

- 2020-02-26
    - Test de integración basados en configuración de entorno.
    - Renombrar los métodos relacionados con el punto de entrada, se llaman `download*()` cuando lo que hacen es
      obtener un `MetadataList`, y el método que crea un objeto de descarga se llama `downloader()`.

- 2020-02-11:
    - Entorno de desarrollo: PhpStan: Nivel máximo sin `checkMissingIterableValueType`.

- 2020-02-11:
    - Entorno de desarrollo: PhpStan: Nivel máximo, aunque se está omitiendo la verificación
      `checkMissingIterableValueType`, corregirla será bastante complejo por las dependencias.

- 2020-01-29:
    - PHP Minimal version to PHP 7.2 (cambios en código, no solamente en composer.json)
    - Incluir badges en README.
    - Travis-CI: construir usando 7.2, 7.3 y 7.4.
    - Scrutinizer-CI: análisis de código y code coverage.
    - Herramientas de desarrollo usando phive o descargando directamente de github, no en composer.json.

- 2020-01-28: Revisión de archivo README, documentación, explicación y ejemplos.

