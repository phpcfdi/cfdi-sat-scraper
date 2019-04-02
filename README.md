
# CFDI-SAT-SCRAPER  

Obtiene las facturas emitidias, recibidas, vigentes y cancelados por medio de web scraping desde la pagina del SAT.

## Instalacion por composer
```
composer require phpcfdi/cfdi-sat-scraper
```

## Ejemplo de descarga por rango de fechas

```php
<?php

require "vendor/autoload.php";

use PhpCfdi\CfdiSatScraper\Exceptions\SATCredentialsException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATAuthenticatedException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATException;
use PhpCfdi\CfdiSatScraper\DeCaptcherCaptchaResolver;
use PhpCfdi\CfdiSatScraper\Filters\StateVoucher;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Options;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Client;

$client = new Client(['timeout' => 0]);
$cookie = new CookieJar();
$captchaResolver = new DeCaptcherCaptchaResolver($client, 'user', 'password');
$loginUrl = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATUPCFDiCon&sid=0&option=credential&sid=0';

$options = new Options([
    'rfc' => 'XAXX010101000',
    'ciec' => '123456',
    'loginUrl' => $loginUrl
]);

$satScraper = new SATScraper($options, $client, $cookie);

$satScraper->setCaptchaResolver($captchaResolver)
    ->setStateVoucher(StateVoucher::ANY)
    ->setDownloadType(DownloadType::RECEIVED)
    ->downloadPeriod(new DateTime('2019-03-01'), new DateTime('2019-03-29'));

print_r($satScraper->getData());
```

## Ejemplo de descarga por lista de uuids

```php
$satScraper->downloadListUUID([
    '5cc88a1a-8672-11e6-ae22-56b6b6499611',
    '5cc88c4a-8672-11e6-ae22-56b6b6499611',
    '5cc88d4e-8672-11e6-ae22-56b6b6499611'
]);

print_r($satScraper->getData());
```

## Excepciones
```php
try {
    $satScraper->setCaptchaResolver($captchaResolver)
        ->setStateVoucher(StateVoucher::ANY)
        ->setDownloadType(DownloadType::RECEIVED)
        ->downloadPeriod(new DateTime('2019-03-01'), new DateTime('2019-03-29'));
} catch(SATCredentialsException $e) { //Error de credenciales
    echo $e->getMessage();
} catch(SATAuthenticatedException $e) { //Error en login, posible cambio en metodo de login (SAT)
    echo $e->getMessage();
}

```

## Comprobar si existen errores de 500 comprobantes en el mismo segundo
```php
$satScraper = new SATScraper($options, $client, $cookie);

$satScraper->setCaptchaResolver($captchaResolver)
    ->setStateVoucher(StateVoucher::ANY)
    ->setDownloadType(DownloadType::RECEIVED)
    ->setOnFiveHundred(function($data){
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
    	print_r($data); // Esta función se ejecutara cada que destecte 500 o mas comprobantes en el mismo segundo
    })
    ->downloadPeriod(new DateTime('2019-03-01'), new DateTime('2019-03-29'));

print_r($satScraper->getData());

```

## Descargar CFDIS a una carpeta

```php
$satScraper = new SATScraper($options, $client, $cookie);

$satScraper->setCaptchaResolver($captchaResolver)
    ->setStateVoucher(StateVoucher::ANY)
    ->setDownloadType(DownloadType::RECEIVED)
    ->downloadPeriod(new DateTime('2019-03-01'), new DateTime('2019-03-29'));
    
    $satScraper->downloader()
            ->setConcurrency(50)
            ->saveTo('downloads', true, 0777);
 
```

## Obtener cada descarga de CFDI

```php
$satScraper = new SATScraper($options, $client, $cookie);

$satScraper->setCaptchaResolver($captchaResolver)
    ->setStateVoucher(StateVoucher::ANY)
    ->setDownloadType(DownloadType::RECEIVED)
    ->downloadPeriod(new DateTime('2019-03-01'), new DateTime('2019-03-29'));
    
    $satScraper->downloader()
            ->setConcurrency(50)
            ->download(function ($content, $name) use ($path) {
              /**
              * @var $content XML string
              * @var $name name of file
              */
            });
 
```
