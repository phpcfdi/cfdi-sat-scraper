# Test de integración

Las pruebas de integración realizan consultas al sitio web del SAT y por lo tanto,
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
usando PHPUnit. El problema viene con el *captcha* del SAT, pues como se generan consultas reales es necesario
poder resolverlo.
Te recomiendo usar [eclipxe13/captcha-local-resolver](https://github.com/eclipxe13/captcha-local-resolver)
para que tú directamente puedas resolver los captchas desde un navegador.

## Ejecución con `eclipxe/captcha-local-resolver`

Lee la documentación de <https://github.com/eclipxe13/captcha-local-resolver>.

- Ejecuta el servicio con un host y puerto.
- Abre tu navegador apuntando a la dirección donde ejecutaste el servicio.
- Configura el entorno para conectar los tests con el resolvedor.
- Corre los tests, en cuando se requiera un captcha lo verás en el navegador, lo resuelves y listo.

Con la siguiente configuración:

```dotenv
CAPTCHA_LOCAL_URL="localhost:9595"
```

La ejecución sería de la siguiente manera:

```shell
cd ../captcha-local-resolver/
php bin/service.php 127.0.0.1:9595
xdg-open http://127.0.0.1:9595
```

Y para ejecutar los tests:

```
# using .env config
php vendor/bin/phpunit --testsuite integration --verbose

# overriding SAT_AUTH_MODE (use CIEC or FIEL)
SAT_AUTH_MODE="FIEL" php vendor/bin/phpunit --testsuite integration --verbose
```
