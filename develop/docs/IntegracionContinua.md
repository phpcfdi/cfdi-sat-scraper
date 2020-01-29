# Integracion continua

## Travis-CI

Se está usando Travis-CI (versión .com) con la cuenta de la organización PHPCFDI.

El proceso de integración construye sobre las versiones soportadas y debe siempre de reportar una construcción
exitosa. En esta plataforma se verifican todos los tests (excepto integración), esto es: estilo del código,
pruebas unitarias y análisis estático.

## Scrutinizer-CI

Se está usando Scrutinizer-CI con la cuenta de la organización PHPCFDI.

A diferencia de Travis-CI, en esta plataforma únicamente se ejecuta el análisis de código de scrutinizer
y la generación del *code coverage*, para ser mostrada en el mismo sitio.
