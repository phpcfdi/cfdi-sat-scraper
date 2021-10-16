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

## Cambios técnicos

Estos cambios son importantes solo si estás desarrollando o extendiendo esta librería.

- Se ha creado la interfaz `SessionManager`, implementada en `CiecSessionManager`
  y `FielSessionManager` para controlar la sesión creada con el SAT.
- Los métodos comunes a las implementaciones de `SessionManager` se han establecido
  en la clase abstracta `AbstractSessionManager`.
- Se han agregado nuevos métodos a `SatHttpGateway`.
- Las constantes de URL han sido renombradas para mayor simplicidad.

Se ha cambiado el archivo de entorno de pruebas `tests/.env-example` agregando nuevas variables.
Para correr los test de integración se recomienda configurar tanto la Clave CIEC como la FIEL.
