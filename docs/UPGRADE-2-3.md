# Guía de actualización de `2.x` a `3.x`

## Creación del objeto `SatScraper`

Anteriormente el objeto `SatScraper` se construía utilizando los datos de la Clave CIEC,
sin embargo, a partir de la versión 3 se puede utilizar la Clave CIEC o la FIEL.

Luego entonces, esta es la nueva forma de construirlo:

```diff
- new SatScraper(new SatSessionData('rfc', 'ciec', $captchaResolver));
+ new SatScraper(CiecSessionManager::create('rfc', 'ciec', $captchaResolver));
```

El método `SatScraper::registerOnPortalMainPage` fue renombrado a `SatScraper::accessPortalMainPage`.
Este método es útil si se está comprobando si actualmente existe una sesión válida
sin necesidad de volver a construirla.

La clase `SatSessionData` ya no existe, ahora su equivalente es `CiecSessionData`.

Ahora la clase `LoginException` es abstracta, y hay especializaciones en
`CiecLoginException` y `FielLoginException`, con ambas se pueden acceder a los
objetos de datos de sesión.

## Resolución de captchas

Se cambió la extracción de captchas a la librería [`phpcfdi/image-captcha-resolver`](https://github.com/phpcfdi/image-captcha-resolver).

Con este cambio, la interfaz de esta librería fue eliminada y ahora se usa la de *Image Captcha Resolver*.

```diff
- use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
+ use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
```

El servicio de *DeCaptcher* ya no está soportado debido a malas experiencias de varios usuarios de la librería,
si lo deseas, podríamos integrarlo en la librería de *Image Captcha Resolver*, por favor considera poder
patrocinar una cuenta para poder ejecutar pruebas de integración.

Anteriormente, en `LoginException` se ponía el captcha en `image`. En la nueva clase `CiecLoginException`
se incluye el método `getCaptchaImage` con el último objeto `CaptchaImage` que no se pudo resolver.

## Manejador del momento cuando se han alcanzado 500 registros

Anteriormente se usaba una función `callable` con la firma `callable(DateTimeImmutable): void`.

Ahora se requiere una implementación del *contrato* `MaximumRecordsHandler`.
Si al crear el objeto `SatScraper` no se establece un manejador o se establece como `null` entonces se usará
una instancia de `NullMaximumRecordsHandler` que, como su nombre lo indica, no realiza ninguna acción.

Si no estaba utilizando esta característica no es necesario hacer nada.

Este es un ejemplo de cómo puede modificar su código para pasar de `callable` a `MaximumRecordsHandler`:

```php
<?php declare(strict_types=1);

use PhpCfdi\CfdiSatScraper\Contracts\MaximumRecordsHandler;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;

// Antes
$onFiveHundred = function (DateTimeImmutable $date) {
    echo 'Se encontraron más de 500 CFDI en el segundo: ', $date->format('c'), PHP_EOL;
};

// Ahora
class MyHandler implements MaximumRecordsHandler
{
    public function handle(DateTimeImmutable $moment) : void
    {
        echo 'Se encontraron más de 500 CFDI en el segundo: ', $date->format('c'), PHP_EOL;
    }
}
$onFiveHundred = new MyHandler();

/**
 * @var SessionManager $sessionManager
 * @var SatHttpGateway $httpGateway
 */
$satScraper = new SatScraper($sessionManager, $httpGateway, $onFiveHundred);
```

## Cambios técnicos

Estos cambios son importantes solo si estás desarrollando o extendiendo esta librería.

- Se ha creado la interfaz `SessionManager`, implementada en `CiecSessionManager`
  y `FielSessionManager` para controlar la sesión creada con el SAT.
- Los métodos comunes a las implementaciones de `SessionManager` se han establecido
  en la clase abstracta `AbstractSessionManager`.
- Se han agregado nuevos métodos a `SatHttpGateway`.
- Las constantes de URL han sido renombradas para mayor simplicidad.
- Las clases `Enum` ahora son finales.

Se ha cambiado el archivo de entorno de pruebas `tests/.env-example` agregando nuevas variables.
Para correr los test de integración se recomienda configurar tanto la Clave CIEC como la FIEL.
