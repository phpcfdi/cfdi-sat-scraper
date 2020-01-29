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

## UNRELEASED 2020-01-28

- Se corrigió el problema de descargas del SAT, la librería estaba en un estado no usable.
- Se inició con el proceso de llevar la librería a una versión mayor `1.0.0`.
