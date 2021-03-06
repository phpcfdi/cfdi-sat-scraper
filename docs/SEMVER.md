# SEMVER

Respetamos el estándar [Versionado Semántico 2.0.0](https://semver.org/lang/es/).

En resumen, [SemVer](https://semver.org/) es un sistema de versiones de tres componentes `X.Y.Z`
que nombraremos así: ` Breaking . Feature . Fix `, donde:

- `Breaking`: Rompe la compatibilidad de código con versiones anteriores.
- `Feature`: Agrega una nueva característica que es compatible con lo anterior.
- `Fix`: Incluye algún cambio (generalmente correcciones) que no agregan nueva funcionalidad.

## Composer

La herramienta [Composer](https://getcomposer.org/) es un gestor de dependencias en proyectos para PHP.
Este gestor usa las [reglas](https://getcomposer.org/doc/articles/versions.md)
de versionado semántico para instalar y actualizar paquetes.

Te recomendamos instalar dependencias de librerías (no frameworks) con *Caret Version Range*.
Por ejemplo: `"vendor/package": "^2.5"`.

Esto significa que:

- no debe actualizar a versiones `3.x.x`
- no debe utilizar ninguna versión menor a `2.5.0`

## Versiones 0.x.y no rompe compatibilidad

Las versiones que inician con cero, por ejemplo `0.y.z`, no se ajustan a las reglas de versionado.
Se considera que estas versiones son previas a la madurez del proyecto y por lo tanto
introducen cambios sin previo aviso.

## `@internal` no rompe compatibilidad

Si la librería contiene elementos marcados como `@internal` significa que no deben ser utilizados
por tu código. Son partes de código internos de la librería.
Por lo tanto, no se consideran breaking changes.

Cuando un elemento es `@internal`, dicho elemento:

- no debe ser una entrada (parámetro)
- no debe ser una salida (retorno)
- no debe exponer funcionalidades en los objetos públicos (rasgos)
