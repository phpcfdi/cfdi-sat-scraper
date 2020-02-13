<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use PhpCfdi\CfdiSatScraper\Internal\Headers;

class SatHttpGateway
{
    /** @var ClientInterface */
    private $client;

    public function __construct(?ClientInterface $client = null, ?CookieJarInterface $cookieJar = null)
    {
        $client = $client ?? new Client();

        // set CookieJar if it was specified from constructor, does not matter if client already has one
        if (null !== $cookieJar) {
            $client = $this->reconstructClientWithCookieJar($client, $cookieJar);
        }

        // set new CookieJar if current is not an instance of CookieJarInterface
        if (! $client->getConfig(RequestOptions::COOKIES) instanceof CookieJarInterface) {
            $client = $this->reconstructClientWithCookieJar($client, new CookieJar());
        }

        $this->client = $client;
    }

    private function reconstructClientWithCookieJar(ClientInterface $client, CookieJarInterface $cookieJar): ClientInterface
    {
        if ($client->getConfig(RequestOptions::COOKIES) === $cookieJar) {
            return $client;
        }

        /** @var array $currentConfig */
        $currentConfig = $client->getConfig();
        return new Client([RequestOptions::COOKIES => $cookieJar] + $currentConfig);
    }

    public function getAuthLoginPage(string $loginUrl): string
    {
        $html = $this->client->request(
            'GET',
            $loginUrl
        )->getBody()->getContents();
        if ('' === $html) {
            throw new \RuntimeException('Unable to retrive the contents from login page');
        }
        return $html;
    }

    public function getPortalMainPage(): string
    {
        return $this->client->request(
            'GET',
            URLS::SAT_URL_PORTAL_CFDI
        )->getBody()->getContents();
    }

    public function postPortalMainPage(array $formData): string
    {
        return $this->client->request(
            'POST',
            URLS::SAT_URL_PORTAL_CFDI,
            [RequestOptions::FORM_PARAMS => $formData]
        )->getBody()->getContents();
    }

    public function postLoginData(string $loginUrl, string $rfc, string $ciec, string $captcha): string
    {
        return $this->client->request(
            'POST',
            $loginUrl,
            [
                RequestOptions::HEADERS => Headers::post(
                    parse_url(URLS::SAT_URL_LOGIN, PHP_URL_HOST),
                    URLS::SAT_URL_LOGIN
                ),
                RequestOptions::FORM_PARAMS => [
                    'Ecom_Password' => $ciec,
                    'Ecom_User_ID' => $rfc,
                    'option' => 'credential',
                    'submit' => 'Enviar',
                    'userCaptcha' => $captcha,
                ],
            ]
        )->getBody()->getContents();
    }

    public function getPortalPage(string $url): string
    {
        return $this->client->request(
            'GET',
            $url
        )->getBody()->getContents();
    }

    public function postAjaxSearch(string $url, array $formParams): string
    {
        return $this->client->request(
            'POST',
            $url,
            [
                RequestOptions::FORM_PARAMS => $formParams,
                RequestOptions::HEADERS => Headers::postAjax(
                    parse_url(URLS::SAT_URL_PORTAL_CFDI, PHP_URL_HOST),
                    $url
                ),
            ]
        )->getBody()->getContents();
    }

    public function getAsync(string $link): PromiseInterface
    {
        return $this->client->requestAsync('GET', $link);
    }
}
