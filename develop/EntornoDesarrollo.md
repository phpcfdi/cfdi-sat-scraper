# Entorno de desarrollo de este proyecto

## Instalación

Para crear un entorno de desarrollo es necesario obtener el repositorio, instalar las dependencias de las
librerías e instalar las dependencias de desarrollo.

```shell
# clonación del proyecto
git clone https://github.com/phpcfdi/cfdi-sat-scraper.git
# posicionarse en la carpeta del proyecto
cd cfdi-sat-scraper
# instalar dependencias de la librería
composer install
# instalar dependencias de desarrollo
composer dev:install
```

## Tests

Un buen paso podría ser iniciar ejecutando los tests unitarios

```shell
# ejecutando phpunit
vendor/bin/phpunit tests/Unit --testdox --verbose
```

Y después los tests de integración, sin embargo es probable que necesites configurar el archivo `tests/.env`.
Revisa la información en [TestIntegracion](TestIntegracion.md)

```shell
# ejecutando phpunit
vendor/bin/phpunit tests/Integration --testdox --verbose
```

## Recomendaciones de cambios

Si estás solucionando un problema que has encontrado, lo mejor es escribir un test para demostrar que el problema
existe y hacer las correcciones, esperando que el test pase correctamente.

Si quieres agregar nuevas funcionalidades te recomiendo que primero abras un issue o entres al canal de la comunidad
para discutir tu idea y como implementarla.

No todo es código, también los cambios pueden hacerse a la documentación, ejemplos de código, integración, etc.

## Cambios en el proyecto

La rama `main` es la rama de trabajo, antes de enviar una solicitud de cambio (Pull Request) asegúrate de
que la estás haciendo sobre esa rama.

La rama `main` se considera una rama estable y puede integrar cambios que aún no han sido liberados en un *release*.

Asegúrate que nada esté roto con los cambios, para revisarlo actualiza las dependencias y apóyate en estos comandos:

```shell
# run default test process
composer dev:build
# run integration tests (not included by default)
vendor/bin/phpunit tests/Integration --testdox --verbose
```
