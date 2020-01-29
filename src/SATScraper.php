<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PhpCfdi\CfdiSatScraper\Captcha\CaptchaBase64Extractor;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\SATAuthenticatedException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATCredentialsException;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;

class SATScraper
{
    public const SAT_CREDENTIAL_ERROR = 'El RFC o CIEC son incorrectos';

    /** @var string */
    protected $rfc;

    /** @var string */
    protected $ciec;

    /** @var Client */
    protected $client;

    /** @var CookieJar */
    protected $cookie;

    /** @var callable|null */
    protected $onFiveHundred;

    /** @var string */
    protected $loginUrl;

    /** @var CaptchaResolverInterface */
    protected $captchaResolver;

    /** @var int */
    protected $maxTriesCaptcha = 3;

    /** @var int */
    protected $maxTriesLogin = 3;

    public function __construct(
        string $rfc,
        string $ciec,
        Client $client,
        CookieJar $cookie,
        CaptchaResolverInterface $captchaResolver
    ) {
        if (empty($rfc)) {
            throw new \InvalidArgumentException('The parameter rfc is invalid');
        }

        if (empty($ciec)) {
            throw new \InvalidArgumentException('The parameter ciec is invalid');
        }

        $this->rfc = $rfc;
        $this->ciec = $ciec;
        $this->loginUrl = URLS::SAT_URL_LOGIN;
        $this->client = $client;
        $this->cookie = $cookie;
        $this->captchaResolver = $captchaResolver;
    }

    public function getLoginUrl(): string
    {
        return $this->loginUrl;
    }

    public function setLoginUrl(string $loginUrl): self
    {
        if (! filter_var($loginUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('The provided url is invalid');
        }

        $this->loginUrl = $loginUrl;

        return $this;
    }

    public function getCaptchaResolver(): CaptchaResolverInterface
    {
        return $this->captchaResolver;
    }

    public function setCaptchaResolver(CaptchaResolverInterface $captchaResolver): self
    {
        $this->captchaResolver = $captchaResolver;

        return $this;
    }

    public function getMaxTriesCaptcha(): int
    {
        return $this->maxTriesCaptcha;
    }

    public function setMaxTriesCaptcha(int $maxTriesCaptcha): self
    {
        $this->maxTriesCaptcha = $maxTriesCaptcha;

        return $this;
    }

    public function getMaxTriesLogin(): int
    {
        return $this->maxTriesLogin;
    }

    public function setMaxTriesLogin(int $maxTriesLogin): self
    {
        $this->maxTriesLogin = $maxTriesLogin;

        return $this;
    }

    public function getCookie(): CookieJar
    {
        return $this->cookie;
    }

    public function setCookie(CookieJar $cookieJar): self
    {
        $this->cookie = $cookieJar;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getOnFiveHundred(): ?callable
    {
        return $this->onFiveHundred;
    }

    public function setOnFiveHundred(?callable $callback): self
    {
        $this->onFiveHundred = $callback;

        return $this;
    }

    /**
     * @param DownloadTypesOption $downloadType
     * @return SATScraper
     *
     * @throws SATAuthenticatedException
     * @throws SATCredentialsException
     */
    public function initScraper(DownloadTypesOption $downloadType): self
    {
        $this->login(1);
        $data = $this->dataAuth();
        $data = $this->postDataAuth($data);
        $data = $this->start($data);
        $this->selectType($downloadType, $data);

        return $this;
    }

    protected function requestCaptchaImage(): string
    {
        $html = $this->client->get(
            $this->loginUrl,
            [
                'future' => true,
                'verify' => false,
                'cookies' => $this->cookie,
            ]
        )->getBody()->getContents();
        if ('' === $html) {
            throw new \RuntimeException('Unable to retrive contents from login page');
        }

        $captchaBase64Extractor = new CaptchaBase64Extractor($html);
        $imageBase64 = $captchaBase64Extractor->retrieve();
        if ('' === $imageBase64) {
            throw new \RuntimeException('Unable to extract the base64 image from login page');
        }

        return $imageBase64;
    }

    protected function getCaptchaValue(int $attempt): ?string
    {
        $imageBase64 = $this->requestCaptchaImage();
        try {
            $result = $this->captchaResolver
                ->setImage($imageBase64)
                ->decode();
            if (null === $result || '' === $result) {
                throw new \RuntimeException('Unable to decode captcha image');
            }
            return $result;
        } catch (\Throwable $exception) {
            if ($attempt < $this->maxTriesCaptcha) {
                return $this->getCaptchaValue($attempt + 1);
            }

            throw $exception;
        }
    }

    protected function login(int $attempt): string
    {
        $response = $this->client->post(
            $this->loginUrl,
            [
                'future' => true,
                'verify' => false,
                'cookies' => $this->cookie,
                'headers' => Headers::post(
                    URLS::SAT_HOST_CFDI_AUTH,
                    URLS::SAT_URL_LOGIN
                ),
                'form_params' => [
                    'Ecom_Password' => $this->ciec,
                    'Ecom_User_ID' => $this->rfc,
                    'option' => 'credential',
                    'submit' => 'Enviar',
                    'userCaptcha' => $this->getCaptchaValue(1),
                ],
            ]
        )->getBody()->getContents();

        if (false !== strpos($response, 'Ecom_User_ID')) {
            if ($attempt < $this->maxTriesLogin) {
                return $this->login($attempt + 1);
            }

            throw new SATCredentialsException(self::SAT_CREDENTIAL_ERROR);
        }

        return $response;
    }

    protected function dataAuth(): array
    {
        $response = $this->client->get(
            URLS::SAT_URL_PORTAL_CFDI,
            [
                'future' => true,
                'cookies' => $this->cookie,
                'verify' => false,
            ]
        )->getBody()->getContents();
        $inputs = $this->parseInputs($response);

        return $inputs;
    }

    /**
     * @param array $inputs
     * @return array
     *
     * @throws SATAuthenticatedException
     */
    protected function postDataAuth(array $inputs): array
    {
        try {
            $response = $this->client->post(
                URLS::SAT_URL_PORTAL_CFDI,
                [
                    'future' => true,
                    'cookies' => $this->cookie,
                    'verify' => false,
                    'form_params' => $inputs,
                ]
            )->getBody()->getContents();
            $inputs = $this->parseInputs($response);

            return $inputs;
        } catch (ClientException $e) {
            throw new SATAuthenticatedException($e->getMessage());
        }
    }

    protected function start(array $inputs = []): array
    {
        $response = $this->client->post(
            URLS::SAT_URL_PORTAL_CFDI,
            [
                'future' => true,
                'cookies' => $this->cookie,
                'verify' => false,
                'form_params' => $inputs,
            ]
        )->getBody()->getContents();
        $inputs = $this->parseInputs($response);

        return $inputs;
    }

    protected function selectType(DownloadTypesOption $downloadType, array $inputs): string
    {
        $data = [
            'ctl00$MainContent$TipoBusqueda' => $downloadType->value(),
            '__ASYNCPOST' => 'true',
            '__EVENTTARGET' => '',
            '__EVENTARGUMENT' => '',
            'ctl00$ScriptManager1' => 'ctl00$MainContent$UpnlBusqueda|ctl00$MainContent$BtnBusqueda',
        ];

        $data = array_merge($inputs, $data);

        $response = $this->client->post(
            URLS::SAT_URL_PORTAL_CFDI_CONSULTA,
            [
                'future' => true,
                'cookies' => $this->cookie,
                'verify' => false,
                'form_params' => $data,
                'headers' => Headers::post(
                    URLS::SAT_HOST_CFDI_AUTH,
                    URLS::SAT_URL_PORTAL_CFDI
                ),
            ]
        )->getBody()->getContents();

        return $response;
    }

    public function createMetadataDownloader(): MetadataDownloader
    {
        return new MetadataDownloader(
            new QueryResolver($this->getClient(), $this->getCookie()),
            $this->onFiveHundred
        );
    }

    public function downloadListUUID(array $uuids, DownloadTypesOption $downloadType): MetadataList
    {
        $this->initScraper($downloadType);
        return $this->createMetadataDownloader()->downloadByUuids($uuids, $downloadType);
    }

    public function downloadPeriod(Query $query): MetadataList
    {
        $this->initScraper($query->getDownloadType());
        return $this->createMetadataDownloader()->downloadByDate($query);
    }

    public function downloadByDateTime(Query $query): MetadataList
    {
        $this->initScraper($query->getDownloadType());
        return $this->createMetadataDownloader()->downloadByDateTime($query);
    }

    protected function parseInputs($html): array
    {
        $htmlForm = new HtmlForm($html, 'form');
        $inputs = $htmlForm->getFormValues();

        return $inputs;
    }

    public function downloader(): DownloadXML
    {
        return new DownloadXML($this->getClient(), $this->getCookie());
    }
}
