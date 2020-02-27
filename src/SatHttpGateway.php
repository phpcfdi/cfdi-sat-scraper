<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayClientException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayResponseException;
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

    /**
     * @param string $url
     * @return string
     * @throws SatHttpGatewayException
     */
    public function getAuthLoginPage(string $url): string
    {
        return $this->get('get login page', $url);
    }

    /**
     * @return string
     * @throws SatHttpGatewayException
     */
    public function getPortalMainPage(): string
    {
        return $this->get('get portal main page', URLS::SAT_URL_PORTAL_CFDI);
    }

    /**
     * @param array $formData
     * @return string
     * @throws SatHttpGatewayException
     */
    public function postPortalMainPage(array $formData): string
    {
        return $this->post('post to portal main page', URLS::SAT_URL_PORTAL_CFDI, Headers::post('', ''), $formData);
    }

    /**
     * @param string $loginUrl
     * @param array $formParams
     * @return string
     * @throws SatHttpGatewayException
     */
    public function postLoginData(string $loginUrl, array $formParams): string
    {
        $headers = Headers::post(parse_url(URLS::SAT_URL_LOGIN, PHP_URL_HOST), URLS::SAT_URL_LOGIN);
        return $this->post('post login data', $loginUrl, $headers, $formParams);
    }

    /**
     * @param string $url
     * @return string
     * @throws SatHttpGatewayException
     */
    public function getPortalPage(string $url): string
    {
        return $this->get('get portal page', $url);
    }

    /**
     * @param string $url
     * @param array $formParams
     * @return string
     * @throws SatHttpGatewayException
     */
    public function postAjaxSearch(string $url, array $formParams): string
    {
        $headers = Headers::postAjax(parse_url(URLS::SAT_URL_PORTAL_CFDI, PHP_URL_HOST), $url);
        return $this->post('query search page', $url, $headers, $formParams);
    }

    /**
     * Create a promise (asyncronic request) to perform an XML download.
     *
     * @param string $link
     * @return PromiseInterface
     */
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

    /**
     * Helper to make a GET request
     *
     * @param string $reason
     * @param string $url
     * @return string
     * @throws SatHttpGatewayException
     */
    private function get(string $reason, string $url): string
    {
        $headers = Headers::get();
        $options = [
            RequestOptions::HEADERS => $headers,
            RequestOptions::COOKIES => $this->cookieJar,
        ];
        try {
            $response = $this->client->request('GET', $url, $options);
        } catch (GuzzleException $exception) {
            throw SatHttpGatewayClientException::clientException($reason, 'GET', $url, $headers, [], $exception);
        }
        $contents = strval($response->getBody());
        if ('' === $contents) {
            throw SatHttpGatewayResponseException::unexpectedEmptyResponse($reason, $response, 'GET', $url, $headers);
        }
        return $contents;
    }

    /**
     * Helper to make a POST request
     *
     * @param string $reason
     * @param string $url
     * @param array $headers
     * @param array $data
     * @return string
     * @throws SatHttpGatewayException
     */
    private function post(string $reason, string $url, array $headers, array $data): string
    {
        $options = [
            RequestOptions::HEADERS => $headers,
            RequestOptions::COOKIES => $this->cookieJar,
            RequestOptions::FORM_PARAMS => $data,
        ];
        try {
            $response = $this->client->request('POST', $url, $options);
        } catch (GuzzleException $exception) {
            throw SatHttpGatewayClientException::clientException($reason, 'POST', $url, $headers, $data, $exception);
        }
        $contents = strval($response->getBody());
        if ('' === $contents) {
            throw SatHttpGatewayResponseException::unexpectedEmptyResponse($reason, $response, 'POST', $url, $headers, $data);
        }
        return $contents;
    }
}
