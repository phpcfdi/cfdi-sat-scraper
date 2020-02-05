# phpcfdi/cfdi-sat-scraper tareas pendientes

## Pendientes

- Core:
    - Clases que vienen de Enum deberían ser finales.
- Documentación:
    - Todos los puntos de entrada deben tener phpdoc.
- Entorno de desarrollo:
    - CodeStyle: Subir a PSR-12.
    - PhpStan: Nivel máximo

## Wishlist

- Renombrar los métodos relacionados con el punto de entrada, se llaman `download*()` cuando lo que hacen es
  obtener un `MetadataList`, y el método que crea un objeto de descarga se llama `downloader()`.
- Test de integración basados en configuración de entorno.
- Code coverage 100%.
- Implementar `rector`.

## Realizadas

- 2020-01-29:
    - PHP Minimal version to PHP 7.2 (cambios en código, no solamente en composer.json)
    - Incluir badges en README.
    - Travis-CI: construir usando 7.2, 7.3 y 7.4.
    - Scrutinizer-CI: análisis de código y code coverage.
    - Herramientas de desarrollo usando phive o descargando directamente de github, no en composer.json.
- 2020-01-28: Revisión de archivo README, documentación, explicación y ejemplos.

