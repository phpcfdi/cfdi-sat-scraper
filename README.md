
# CFDI-SAT-SCRAPER  

Obtiene las facturas emitidias, recibidas, vigentes y cancelados por medio de web scraping desde la pagina del SAT.

## Instalacion por composer
```
composer require phpcfdi/cfdi-sat-scraper
```

## Ejemplo de descarga por rango de fechas

```php
<?php

use PhpCfdi\CfdiSatScraper\Exceptions\SATCredentialsException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATAuthenticatedException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATException;
use PhpCfdi\CfdiSatScraper\DeCaptcherCaptchaResolver;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Filters\StatesVoucher;
use PhpCfdi\CfdiSatScraper\Filters\DownloadTypes;
use PhpCfdi\CfdiSatScraper\Filters\Complements;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Query;
use GuzzleHttp\Client;

$client = new Client(['timeout' => 0]);
$cookie = new CookieJar();
$captchaResolver = new DeCaptcherCaptchaResolver($client, 'user', 'password');
$loginUrl = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';

$query = new Query(new DateTime('2019-03-01'), new DateTime('2019-03-31'));

$query//->setRfc(new RfcReceptor('XAXX010101000'))
    ->setComplement(Complements::todos())
    ->setStateVoucher(StatesVoucher::vigentes())
    ->setDownloadType(DownloadTypes::recibidos());

$satScraper = new SATScraper('rfc', 'ciec', $client, $cookie, $captchaResolver);
    //$satScraper->setLoginUrl($loginUrl);
    $satScraper->downloadPeriod($query);

print_r($satScraper->getData());
```

## Ejemplo de descarga por lista de uuids

```php
<?php

use PhpCfdi\CfdiSatScraper\Exceptions\SATCredentialsException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATAuthenticatedException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATException;
use PhpCfdi\CfdiSatScraper\DeCaptcherCaptchaResolver;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Filters\StatesVoucher;
use PhpCfdi\CfdiSatScraper\Filters\DownloadTypes;
use PhpCfdi\CfdiSatScraper\Filters\Complements;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Query;
use GuzzleHttp\Client;

$client = new Client(['timeout' => 0]);
$cookie = new CookieJar();
$captchaResolver = new DeCaptcherCaptchaResolver($client, 'user', 'password');
$loginUrl = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';

$query = new Query(new DateTime('2019-03-01'), new DateTime('2019-03-31'));

$query//->setRfc(new RfcReceptor('XAXX010101000'))
    ->setComplement(Complements::todos())
    ->setStateVoucher(StatesVoucher::vigentes())
    ->setDownloadType(DownloadTypes::recibidos())
    ->setUuid([
      '5cc88a1a-8672-11e6-ae22-56b6b6499611',
      '5cc88c4a-8672-11e6-ae22-56b6b6499611',
      '5cc88d4e-8672-11e6-ae22-56b6b6499611'
    ]);

$satScraper = new SATScraper('rfc', 'ciec', $client, $cookie, $captchaResolver);
$satScraper->downloadListUUID($query);

print_r($satScraper->getData());
```

## Excepciones
```php
<?php

use PhpCfdi\CfdiSatScraper\Exceptions\SATCredentialsException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATAuthenticatedException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATException;
use PhpCfdi\CfdiSatScraper\DeCaptcherCaptchaResolver;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Filters\StatesVoucher;
use PhpCfdi\CfdiSatScraper\Filters\DownloadTypes;
use PhpCfdi\CfdiSatScraper\Filters\Complements;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Query;
use GuzzleHttp\Client;

$client = new Client(['timeout' => 0]);
$cookie = new CookieJar();
$captchaResolver = new DeCaptcherCaptchaResolver($client, 'user', 'password');
$loginUrl = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';

try {
    $query = new Query(new DateTime('2019-03-01'), new DateTime('2019-03-31'));
    
    $query//->setRfc(new RfcReceptor('XAXX010101000'))
        ->setComplement(Complements::todos())
        ->setStateVoucher(StatesVoucher::vigentes())
        ->setDownloadType(DownloadTypes::recibidos());
    
    $satScraper = new SATScraper('rfc', 'ciec', $client, $cookie, $captchaResolver);
    $satScraper->downloadPeriod($query);
    
    print_r($satScraper->getData());
} catch (SATCredentialsException | SATAuthenticatedException $e) {
    print_r($e->getMessage());
} catch (SATException $e) {
    print_r($e->getMessage());
}

```

## Comprobar si existen errores de 500 comprobantes en el mismo segundo
```php
<?php

use PhpCfdi\CfdiSatScraper\Exceptions\SATCredentialsException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATAuthenticatedException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATException;
use PhpCfdi\CfdiSatScraper\DeCaptcherCaptchaResolver;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Filters\StatesVoucher;
use PhpCfdi\CfdiSatScraper\Filters\DownloadTypes;
use PhpCfdi\CfdiSatScraper\Filters\Complements;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Query;
use GuzzleHttp\Client;

$client = new Client(['timeout' => 0]);
$cookie = new CookieJar();
$captchaResolver = new DeCaptcherCaptchaResolver($client, 'user', 'password');
$loginUrl = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';

$query = new Query(new DateTime('2019-03-01'), new DateTime('2019-03-31'));

$query//->setRfc(new RfcReceptor('XAXX010101000'))
->setComplement(Complements::todos())
    ->setStateVoucher(StatesVoucher::vigentes())
    ->setDownloadType(DownloadTypes::recibidos());

$satScraper = new SATScraper('rfc', 'ciec', $client, $cookie, $captchaResolver);
$satScraper->downloadPeriod($query);
$satScraper->setOnFiveHundred(function ($data) {
    /*
    * @var $data contains:
    * [
    *      'count' => 500,
    *      'year' => 2019,
    *      'month' => '03',
    *      'day' => 1,
    *      'secondIni' => 1,
    *      'secondFin' => 86400,
    * ]
    *
    */
    print_r($data); // Esta función se ejecutara cada que detecte 500 o mas comprobantes en el mismo segundo
});

print_r($satScraper->getData());

```

## Descargar CFDIS a una carpeta

```php
<?php

use PhpCfdi\CfdiSatScraper\Exceptions\SATCredentialsException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATAuthenticatedException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATException;
use PhpCfdi\CfdiSatScraper\DeCaptcherCaptchaResolver;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Filters\StatesVoucher;
use PhpCfdi\CfdiSatScraper\Filters\DownloadTypes;
use PhpCfdi\CfdiSatScraper\Filters\Complements;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Query;
use GuzzleHttp\Client;

$client = new Client(['timeout' => 0]);
$cookie = new CookieJar();
$captchaResolver = new DeCaptcherCaptchaResolver($client, 'user', 'password');
$loginUrl = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';

$query = new Query(new DateTime('2019-03-01'), new DateTime('2019-03-31'));

$query//->setRfc(new RfcReceptor('XAXX010101000'))
    ->setComplement(Complements::todos())
    ->setStateVoucher(StatesVoucher::vigentes())
    ->setDownloadType(DownloadTypes::recibidos());

$satScraper = new SATScraper('rfc', 'ciec', $client, $cookie, $captchaResolver);
$satScraper->downloadPeriod($query);

print_r($satScraper->getData());
    
    $satScraper->downloader()
            ->setConcurrency(50)
            ->saveTo('downloads', true, 0777);
 
```

## Obtener cada descarga de CFDI

```php
<?php

use PhpCfdi\CfdiSatScraper\Exceptions\SATCredentialsException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATAuthenticatedException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATException;
use PhpCfdi\CfdiSatScraper\DeCaptcherCaptchaResolver;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Filters\StatesVoucher;
use PhpCfdi\CfdiSatScraper\Filters\DownloadTypes;
use PhpCfdi\CfdiSatScraper\Filters\Complements;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Query;
use GuzzleHttp\Client;

$client = new Client(['timeout' => 0]);
$cookie = new CookieJar();
$captchaResolver = new DeCaptcherCaptchaResolver($client, 'user', 'password');
$loginUrl = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';

$query = new Query(new DateTime('2019-03-01'), new DateTime('2019-03-31'));

$query//->setRfc(new RfcReceptor('XAXX010101000'))
    ->setComplement(Complements::todos())
    ->setStateVoucher(StatesVoucher::vigentes())
    ->setDownloadType(DownloadTypes::recibidos());

$satScraper = new SATScraper('rfc', 'ciec', $client, $cookie, $captchaResolver);
$satScraper->downloadPeriod($query);

print_r($satScraper->getData());
    
$satScraper->downloader()
        ->setConcurrency(50)
        ->download(function ($content, $name) use ($path) {
          /**
          * @var $content XML string
          * @var $name name of file
          */
        });
 
```
