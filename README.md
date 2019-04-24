
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
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\ComplementsOption;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Query;
use GuzzleHttp\Client;

$client = new Client(['timeout' => 0]);
$cookie = new CookieJar();
$captchaResolver = new DeCaptcherCaptchaResolver($client, 'user', 'password');
$loginUrl = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';

$query = new Query(new DateTimeImmutable('2019-03-01'), new DateTimeImmutable('2019-03-31'));

$query//->setRfc(new RfcReceptor('XAXX010101000'))
    ->setComplement(ComplementsOption::todos())
    ->setStateVoucher(StatesVoucherOption::vigentes())
    ->setDownloadType(DownloadTypesOption::recibidos());

$satScraper = new SATScraper('rfc', 'ciec', $client, $cookie, $captchaResolver);
    //$satScraper->setLoginUrl($loginUrl);
    $list = $satScraper->downloadPeriod($query);

print_r($list);
```

## Ejemplo de descarga por lista de uuids

```php
<?php

use PhpCfdi\CfdiSatScraper\Exceptions\SATCredentialsException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATAuthenticatedException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATException;
use PhpCfdi\CfdiSatScraper\DeCaptcherCaptchaResolver;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\ComplementsOption;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Query;
use GuzzleHttp\Client;

$client = new Client(['timeout' => 0]);
$cookie = new CookieJar();
$captchaResolver = new DeCaptcherCaptchaResolver($client, 'user', 'password');
$loginUrl = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';

$query = new Query(new DateTimeImmutable('2019-03-01'), new DateTimeImmutable('2019-03-31'));

$query
    ->setComplement(ComplementsOption::todos())
    ->setStateVoucher(StatesVoucherOption::vigentes())
    ->setDownloadType(DownloadTypesOption::recibidos())
    ->setUuid([
      '5cc88a1a-8672-11e6-ae22-56b6b6499611',
      '5cc88c4a-8672-11e6-ae22-56b6b6499611',
      '5cc88d4e-8672-11e6-ae22-56b6b6499611'
    ]);

$satScraper = new SATScraper('rfc', 'ciec', $client, $cookie, $captchaResolver);
$list = $satScraper->downloadListUUID($query);

print_r($list);
```

## Excepciones
```php
<?php

use PhpCfdi\CfdiSatScraper\Exceptions\SATCredentialsException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATAuthenticatedException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATException;
use PhpCfdi\CfdiSatScraper\DeCaptcherCaptchaResolver;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\ComplementsOption;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Query;
use GuzzleHttp\Client;

$client = new Client(['timeout' => 0]);
$cookie = new CookieJar();
$captchaResolver = new DeCaptcherCaptchaResolver($client, 'user', 'password');
$loginUrl = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';

try {
    $query = new Query(new DateTimeImmutable('2019-03-01'), new DateTimeImmutable('2019-03-31'));
    
    $query//->setRfc(new RfcReceptor('XAXX010101000'))
        ->setComplement(ComplementsOption::todos())
        ->setStateVoucher(StatesVoucherOption::vigentes())
        ->setDownloadType(DownloadTypesOption::recibidos());
    
    $satScraper = new SATScraper('rfc', 'ciec', $client, $cookie, $captchaResolver);
    $list = $satScraper->downloadPeriod($query);
    
    print_r($list);
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
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\ComplementsOption;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Query;
use GuzzleHttp\Client;

$client = new Client(['timeout' => 0]);
$cookie = new CookieJar();
$captchaResolver = new DeCaptcherCaptchaResolver($client, 'user', 'password');
$loginUrl = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';

$query = new Query(new DateTimeImmutable('2019-03-01'), new DateTimeImmutable('2019-03-31'));

$query//->setRfc(new RfcReceptor('XAXX010101000'))
->setComplement(ComplementsOption::todos())
    ->setStateVoucher(StatesVoucherOption::vigentes())
    ->setDownloadType(DownloadTypesOption::recibidos());

$satScraper = new SATScraper('rfc', 'ciec', $client, $cookie, $captchaResolver);
$list = $satScraper->downloadPeriod($query);
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

print_r($list);

```

## Descargar CFDIS a una carpeta

```php
<?php

use PhpCfdi\CfdiSatScraper\Exceptions\SATCredentialsException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATAuthenticatedException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATException;
use PhpCfdi\CfdiSatScraper\DeCaptcherCaptchaResolver;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\ComplementsOption;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Query;
use GuzzleHttp\Client;

$client = new Client(['timeout' => 0]);
$cookie = new CookieJar();
$captchaResolver = new DeCaptcherCaptchaResolver($client, 'user', 'password');
$loginUrl = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';

$query = new Query(new DateTimeImmutable('2019-03-01'), new DateTimeImmutable('2019-03-31'));

$query//->setRfc(new RfcReceptor('XAXX010101000'))
    ->setComplement(ComplementsOption::todos())
    ->setStateVoucher(StatesVoucherOption::vigentes())
    ->setDownloadType(DownloadTypesOption::recibidos());

$satScraper = new SATScraper('rfc', 'ciec', $client, $cookie, $captchaResolver);
$list = $satScraper->downloadPeriod($query);

print_r($list);
    
$satScraper->downloader($list)
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
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\ComplementsOption;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Query;
use GuzzleHttp\Client;

$client = new Client(['timeout' => 0]);
$cookie = new CookieJar();
$captchaResolver = new DeCaptcherCaptchaResolver($client, 'user', 'password');
$loginUrl = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';

$query = new Query(new DateTimeImmutable('2019-03-01'), new DateTimeImmutable('2019-03-31'));

$query//->setRfc(new RfcReceptor('XAXX010101000'))
    ->setComplement(ComplementsOption::todos())
    ->setStateVoucher(StatesVoucherOption::vigentes())
    ->setDownloadType(DownloadTypesOption::recibidos());

$satScraper = new SATScraper('rfc', 'ciec', $client, $cookie, $captchaResolver);
$list = $satScraper->downloadPeriod($query);

print_r($list);
    
$satScraper->downloader($list)
    ->setConcurrency(50)
    ->download(function (string $content, string $name) use ($path) {
      /**
      * @var string $content XML string
      * @var string $name name of file
      */
    });
 
```
