<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\Internal\Headers;

class SatHttpGateway
{
    /** @var ClientInterface */
    private $client;

    /** @var CookieJarInterface */
    private $cookieJar;

    public function __construct(?ClientInterface $client = null, ?CookieJarInterface $cookieJar = null)
    {
        // create a new client (if not set) with the given cookie (if set)
        $client = $client ?? new Client([RequestOptions::COOKIES => $cookieJar ?? new CookieJar()]);

        // if the cookieJar was set on the client but not in the configuration
        if (null === $cookieJar) {
            /** @var mixed $cookieJar */
            $cookieJar = $client->getConfig(RequestOptions::COOKIES);
            if (! $cookieJar instanceof CookieJarInterface) {
                $cookieJar = new CookieJar();
            }
        }

        $this->client = $client;
        $this->cookieJar = $cookieJar;
    }

    public function getAuthLoginPage(string $url): string
    {
        return $this->get('get login page', $url);
    }

    public function getPortalMainPage(): string
    {
        return $this->get('get portal main page', URLS::SAT_URL_PORTAL_CFDI);
    }

    public function postPortalMainPage(array $formData): string
    {
        return $this->post('post to portal main page', URLS::SAT_URL_PORTAL_CFDI, Headers::post('', ''), $formData);
    }

    public function postLoginData(string $loginUrl, array $formParams): string
    {
        $headers = Headers::post(parse_url(URLS::SAT_URL_LOGIN, PHP_URL_HOST), URLS::SAT_URL_LOGIN);
        return $this->post('post login data', $loginUrl, $headers, $formParams);
    }

    public function getPortalPage(string $url): string
    {
        return $this->get('get portal page', $url);
    }

    public function postAjaxSearch(string $url, array $formParams): string
    {
        $headers = Headers::postAjax(parse_url(URLS::SAT_URL_PORTAL_CFDI, PHP_URL_HOST), $url);
        return $this->post('query search page', $url, $headers, $formParams);
    }

    public function getAsync(string $link): PromiseInterface
    {
        $options = [
            RequestOptions::HEADERS => Headers::get(),
            RequestOptions::COOKIES => $this->cookieJar,
        ];
        return $this->client->requestAsync('GET', $link, $options);
    }

    public function clearCookieJar(): void
    {
        $this->cookieJar->clear();
    }

    private function get(string $reason, string $url): string
    {
        $options = [
            RequestOptions::HEADERS => Headers::get(),
            RequestOptions::COOKIES => $this->cookieJar,
        ];
        $response = $this->client->request('GET', $url, $options);
        $contents = $response->getBody()->getContents();
        if ('' === $contents) {
            throw SatHttpGatewayException::unexpectedEmptyResponse($reason, $url, $response);
        }
        return $contents;
    }

    private function post(string $reason, string $url, array $headers, array $data): string
    {
        $options = [
            RequestOptions::HEADERS => $headers,
            RequestOptions::COOKIES => $this->cookieJar,
            RequestOptions::FORM_PARAMS => $data,
        ];
        $response = $this->client->request('POST', $url, $options);
        $contents = $response->getBody()->getContents();
        if ('' === $contents) {
            throw SatHttpGatewayException::unexpectedEmptyResponse($reason, $url, $response);
        }
        return $contents;
    }
}
