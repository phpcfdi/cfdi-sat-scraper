# Test de integración

Las pruebas de integración realizan consultas al sitio web del SAT y, por lo tanto,
como utiliza datos de RFC y Clave CIEC, o bien, utiliza la llave FIEL,
entonces no pueden funcionar igual para todos.

Por lo anterior, partimos de que los tests de integración deben adecuarse a probar con flexibilidad sobre
una *fuente de verdad*. Así entonces, los tests de integración van a verificar que efectivamente se esté
realizando una tarea en particular (como descargar un CFDI por UUID) tomando como fuente de verdad como
los datos esperados.

La configuración de RFC y Clave CIEC o llave FIEL se guardan en variables de entorno.

La *fuente de verdad* le llamamos repositorio y se almacena en una estructura específica en un archivo.

## Configuración de entorno `tests/.env`

Configura el archivo `tests/.env`, puedes usar `tests/.env-example` como referencia.

## Configuración del repositorio `tests/repository.json`

Configura el listado de cfdis esperados en `tests/repository.json`,
usa el archivo `tests/repository-example.json` como muestra.
El archivo debe contener un arreglo de objetos con la siguiente estructura.

```json
[
    {
        "uuid": "",
        "issuer": "",
        "receiver": "",
        "date": "",
        "state": "",
        "type": ""
    }
]
```

Considera los siguientes requisitos:

* `uuid` es el UUID del CFDI.
* `issuer` debe contener el RFC emisor.
* `receiver` debe contener el RFC receptor.
* `date` debe ser una expresión como `2020-01-13 14:15:16`.
* `state` debe ser `C` para cancelado, cualquier otra será tomada como `Activo`.
* `type` debe ser `E` para emitido, cualquier otra será tomada como `Recibido`.

### Generación del repositorio

Puedes usar el archivo `tests/generate-repository.php` para generar la lista, por ejemplo:

```shell
php tests/generate-repository.php "2020-01-01 00:00:00" "2020-01-31 29:59:59" > tests/repository.json
```

## Ejecución de los tests de integración

Una vez que se ha configurado el entorno y el repositorio entonces se pueden ejecutar los tests de integración
usando PHPUnit. Para el *captcha* del SAT implementa `phpcfdi/image-captcha-resolver-boxfactura-ai`.

```shell
# using .env config
php vendor/bin/phpunit --testsuite integration

# overriding SAT_AUTH_MODE (use CIEC or FIEL)
SAT_AUTH_MODE="FIEL" php vendor/bin/phpunit --testsuite integration
```

## Integración con los flujos de trabajo de GitHub

Se ha configurado el proyecto para proveer de forma segura una FIEL, una clave CIEC y un entorno de pruebas seguro.

Los archivos involucrados son:

- `tests/_files/secure/environment`, que se reubica en `tests/.env`. 
- `tests/_files/secure/certificate.cer`
- `tests/_files/secure/private.key`
- `tests/_files/secure/private.pass`
- `tests/_files/secure/repository.json`, que se reubica en `tests/repository.json`.

Para encriptar y desencriptar los archivos se usa el *script* `tests/` que a su vez usa `gpg`.
Esta herramienta requiere de la variable de entorno `ENCFILESKEY`, que se establece en los
flujos de trabajo de GitHub usando los secretos del repositorio.

Solo GitHub tiene la llave de encriptación. En 2025-09-30 fue usada para encriptar los archivos, almacenada y olvidada.

Si estos archivos requieren cambiar -ya sea porque se generó un mejor repositorio, se están especificando nuevas
credenciales CIEC o se ha cambiado la FIEL- es necesario volver a encriptar los archivos con una nueva clave,
almacenar la clave en los secretos de GITHUB e incluir los nuevos archivos en el repositorio.
