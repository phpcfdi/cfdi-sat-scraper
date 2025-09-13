<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayClientException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayResponseException;
use PhpCfdi\CfdiSatScraper\Internal\Headers;
use PhpCfdi\CfdiSatScraper\Internal\MetaRefreshInspector;
use Psr\Http\Message\ResponseInterface;

class SatHttpGateway
{
    private ClientInterface $client;

    private CookieJarInterface $cookieJar;

    private string $effectiveUri;

    public function __construct(?ClientInterface $client = null, ?CookieJarInterface $cookieJar = null)
    {
        // create a new client (if not set) with the given cookie (if set)
        $client ??= new Client([RequestOptions::COOKIES => $cookieJar ?? new CookieJar()]);

        // if the cookieJar was set on the client but not in the configuration
        if (null === $cookieJar) {
            /**
             * @noinspection PhpDeprecationInspection
             * @var mixed $cookieJar
             */
            $cookieJar = $client->getConfig(RequestOptions::COOKIES);
            if (! $cookieJar instanceof CookieJarInterface) {
                $cookieJar = new CookieJar();
            }
        }

        $this->client = $client;
        $this->cookieJar = $cookieJar;
    }

    /**
     * @throws SatHttpGatewayException
     */
    public function getAuthLoginPage(string $url, string $referer = ''): string
    {
        return $this->get('get login page', $url, $referer);
    }

    /**
     * @throws SatHttpGatewayException
     */
    public function getPortalMainPage(): string
    {
        return $this->get('get portal main page', URLS::PORTAL_CFDI);
    }

    /**
     * @param array<string, string> $formData
     * @throws SatHttpGatewayException
     */
    public function postPortalMainPage(array $formData): string
    {
        return $this->post('post to portal main page', URLS::PORTAL_CFDI, Headers::post('', ''), $formData);
    }

    /**
     * @param array<string, string> $formParams
     * @throws SatHttpGatewayException
     */
    public function postCiecLoginData(string $loginUrl, array $formParams): string
    {
        $headers = Headers::post($this->urlHost(URLS::AUTH_LOGIN), URLS::AUTH_LOGIN);
        return $this->post('post login data', $loginUrl, $headers, $formParams);
    }

    /**
     * @param array<string, string> $formParams
     * @throws SatHttpGatewayException
     */
    public function postFielLoginData(string $loginUrl, array $formParams): string
    {
        $headers = Headers::post($this->urlHost($loginUrl), $loginUrl);
        return $this->post('post fiel login data', $loginUrl, $headers, $formParams);
    }

    /**
     * @throws SatHttpGatewayException
     */
    public function getPortalPage(string $url): string
    {
        return $this->get('get portal page', $url);
    }

    /**
     * @param array<string, string> $formParams
     * @throws SatHttpGatewayException
     */
    public function postAjaxSearch(string $url, array $formParams): string
    {
        $headers = Headers::postAjax($this->urlHost(URLS::PORTAL_CFDI), $url);
        return $this->post('query search page', $url, $headers, $formParams);
    }

    /**
     * Create a promise (asynchronous request) to perform an XML download.
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

    public function isCookieJarEmpty(): bool
    {
        return [] === $this->cookieJar->toArray();
    }

    /**
     * Helper to make a GET request
     *
     * @throws SatHttpGatewayClientException
     * @throws SatHttpGatewayResponseException
     */
    private function get(string $reason, string $url, string $referer = ''): string
    {
        $options = [
            RequestOptions::HEADERS => Headers::get($referer),
        ];
        return $this->request('GET', $url, $options, $reason);
    }

    /**
     * Helper to make a POST request
     *
     * @param array<string, mixed> $headers
     * @param array<string, string> $data
     * @throws SatHttpGatewayException
     */
    private function post(string $reason, string $url, array $headers, array $data): string
    {
        $options = [
            RequestOptions::HEADERS => $headers,
            RequestOptions::FORM_PARAMS => $data,
        ];
        return $this->request('POST', $url, $options, $reason);
    }

    /**
     * @param array<string, mixed> $options
     * @throws SatHttpGatewayClientException
     * @throws SatHttpGatewayResponseException
     */
    private function request(string $method, string $uri, array $options, string $reason): string
    {
        $options = [
            RequestOptions::COOKIES => $this->cookieJar,
            RequestOptions::ALLOW_REDIRECTS => ['trackredirects' => true],
        ] + $options;

        $this->effectiveUri = $uri;
        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (GuzzleException $exception) {
            if ($exception instanceof RequestException && null !== $exception->getResponse()) {
                $this->setEffectiveUriFromResponse($exception->getResponse(), $uri);
            } else {
                $this->effectiveUri = $uri;
            }
            /** @var array<string, mixed> $requestHeaders */
            $requestHeaders = $options[RequestOptions::HEADERS];
            /** @var array<string, mixed> $requestData */
            $requestData = $options[RequestOptions::FORM_PARAMS] ?? [];
            throw SatHttpGatewayClientException::clientException(
                $reason,
                $method,
                $uri,
                $requestHeaders,
                $requestData,
                $exception,
            );
        }
        $this->setEffectiveUriFromResponse($response, $uri);

        $contents = strval($response->getBody());
        if ('' === $contents) {
            /** @var array<string, mixed> $requestHeaders */
            $requestHeaders = $options[RequestOptions::HEADERS];
            /** @var array<string, mixed> $requestData */
            $requestData = $options[RequestOptions::FORM_PARAMS] ?? [];
            throw SatHttpGatewayResponseException::unexpectedEmptyResponse(
                $reason,
                $response,
                $method,
                $uri,
                $requestHeaders,
                $requestData,
            );
        }

        return $contents;
    }

    public function getLogout(): string
    {
        $metaRefresh = new MetaRefreshInspector();

        $destination = URLS::PORTAL_CFDI_LOGOUT;
        $referer = URLS::PORTAL_CFDI;

        do {
            $html = $this->getLogoutWithoutException($destination, $referer);
            $referer = $this->getEffectiveUri(); // it can be redirected several
            $destination = $metaRefresh->obtainUrl($html, $referer);
        } while ('' !== $destination && $destination !== $referer);

        $this->clearCookieJar();

        return $html;
    }

    private function getLogoutWithoutException(string $destination, string $referer): string
    {
        try {
            return $this->get('logout', $destination, $referer);
        } catch (SatHttpGatewayException) {
            return '';
        }
    }

    private function getEffectiveUri(): string
    {
        return $this->effectiveUri;
    }

    private function setEffectiveUriFromResponse(ResponseInterface $response, string $previousUri): void
    {
        $history = $response->getHeader('X-Guzzle-Redirect-History');
        $effectiveUri = (string) end($history);
        $this->effectiveUri = $effectiveUri ?: $previousUri;
    }

    private function urlHost(string $url): string
    {
        return (string) parse_url($url, PHP_URL_HOST);
    }
}
