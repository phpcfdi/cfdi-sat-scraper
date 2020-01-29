# phpcfdi/cfdi-sat-scraper tareas pendientes

## Pendientes

- Core:
    - Clases que vienen de Enum deberían ser finales.
    - RfcReceptorOption es el que se debe usar incluso con una consulta de cfdi recibidos.
      Debería tener otro nombre o crear RfcEmisorOption aunque solo sea una extensión de RfcReceptorOption.
- Documentación:
    - Incluir badges en README.
    - Todos los puntos de entrada deben tener phpdoc.
- Entorno de desarrollo:
    - Travis-CI: construir usando 7.2, 7.3 y 7.4.
    - Scrutinizer-CI: análisis de código y code coverage.
    - CodeStyle: Subir a PSR-12.
    - PhpStan: Nivel máximo

## Wishlist

- Renombrar los métodos relacionados con el punto de entrada, se llaman `download*()` cuando lo que hacen es
  obtener un `MetadataList`, y el método que crea un objeto de descarga se llama `downloader()`.
- Test de integración basados en configuración de entorno.
- Code coverage 100%.
- Implementar `rector`.

## Realizadas

- 2020-01-28: Revisión de archivo README, documentación, explicación y ejemplos.

