<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Contracts\Filters;
use PhpCfdi\CfdiSatScraper\Exceptions\SATAuthenticatedException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATCredentialsException;
use PhpCfdi\CfdiSatScraper\Filters\FiltersIssued;
use PhpCfdi\CfdiSatScraper\Filters\FiltersReceived;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;

/**
 * Class SATScraper.
 */
class SATScraper
{
    public const SAT_CREDENTIAL_ERROR = 'El RFC o CIEC son incorrectos';

    /**
     * @var string
     */
    protected $rfc;

    /**
     * @var string
     */
    protected $ciec;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var CookieJar
     */
    protected $cookie;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $requests = [];

    /**
     * @var Query
     */
    protected $query;

    /**
     * @var null
     */
    protected $onFiveHundred = null;

    /**
     * @var string
     */
    protected $loginUrl;

    /**
     * @var CaptchaResolverInterface
     */
    protected $captchaResolver;

    /**
     * @var int
     */
    protected $maxTriesCaptcha = 3;

    /**
     * @var int
     */
    private $triesCaptcha = 0;

    /**
     * @var int
     */
    protected $maxTriesLogin = 3;

    /**
     * @var int
     */
    private $triesLogin = 0;

    /**
     * SATScraper constructor.
     * @param string $rfc
     * @param string $ciec
     * @param Client $client
     * @param CookieJar $cookie
     * @param CaptchaResolverInterface $captchaResolver
     */
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

    /**
     * @param string $loginUrl
     * @return SATScraper
     */
    public function setLoginUrl(string $loginUrl): self
    {
        if (! filter_var($loginUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('The provided url is invalid');
        }

        $this->loginUrl = $loginUrl;

        return $this;
    }

    /**
     * @param CaptchaResolverInterface $captchaResolver
     *
     * @return SATScraper
     */
    public function setCaptchaResolver(CaptchaResolverInterface $captchaResolver): self
    {
        $this->captchaResolver = $captchaResolver;

        return $this;
    }

    /**
     * @param int $maxTriesCaptcha
     *
     * @return SATScraper
     */
    public function setMaxTriesCaptcha(int $maxTriesCaptcha): self
    {
        $this->maxTriesCaptcha = $maxTriesCaptcha;

        return $this;
    }

    /**
     * @param int $maxTriesLogin
     *
     * @return SATScraper
     */
    public function setMaxTriesLogin(int $maxTriesLogin): self
    {
        $this->maxTriesLogin = $maxTriesLogin;

        return $this;
    }

    /**
     * @param DownloadTypesOption $downloadType
     * @return SATScraper
     * @throws SATAuthenticatedException
     * @throws SATCredentialsException
     */
    public function initScraper(DownloadTypesOption $downloadType): self
    {
        $this->login();
        $data = $this->dataAuth();
        $data = $this->postDataAuth($data);
        $data = $this->start($data);
        $this->selectType($downloadType, $data);

        return $this;
    }

    /**
     * @return string|null
     */
    protected function getCaptchaValue(): ?string
    {
        try {
            $image = $this->client->get(
                URLS::SAT_URL_CAPTCHA,
                [
                    'future' => true,
                    'verify' => false,
                    'cookies' => $this->cookie,
                ]
            )->getBody()->getContents();

            $imageBase64 = base64_encode($image);

            return $this->captchaResolver
                ->setImage($imageBase64)
                ->decode();
        } catch (ConnectException $e) {
            if ($this->triesCaptcha < $this->maxTriesCaptcha) {
                $this->triesCaptcha++;
                return $this->getCaptchaValue();
            }

            throw new $e();
        }
    }

    /**
     * @return string
     * @throws SATCredentialsException
     */
    protected function login(): string
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
                    'jcaptcha' => $this->getCaptchaValue(),
                ],
            ]
        )->getBody()->getContents();

        if (false !== strpos($response, 'Ecom_User_ID')) {
            if ($this->triesLogin < $this->maxTriesLogin) {
                $this->triesLogin++;
                return $this->login();
            }

            throw new SATCredentialsException(self::SAT_CREDENTIAL_ERROR);
        }

        return $response;
    }

    /**
     * @return array
     */
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
     * @param  array $inputs
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

    /**
     * @param array $inputs
     *
     * @return array
     */
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

    /**
     * @param DownloadTypesOption $downloadType
     * @param array $inputs
     *
     * @return string
     */
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

    /**
     * @param array $uuids
     * @param DownloadTypesOption $downloadType
     * @throws \Exception
     */
    public function downloadListUUID(array $uuids, DownloadTypesOption $downloadType): void
    {
        $query = new Query(new \DateTimeImmutable(), new \DateTimeImmutable());
        $this->data = [];
        $filters = $downloadType->isEmitidos() ? new FiltersIssued($query) : new FiltersReceived($query);

        foreach ($uuids as $uuid) {
            $filters->setUuid($uuid);
            $html = $this->runQueryDate($query, $filters);
            $this->makeData($html);
        }
    }

    /**
     * @param Query $query
     * @throws SATAuthenticatedException
     * @throws SATCredentialsException
     */
    public function downloadPeriod(Query $query): void
    {
        $this->initScraper($query->getDownloadType());
        $start = $query->getStartDate()->setTime(0, 0, 0);
        $end = $query->getEndDate()->setTime(0, 0, 0);

        $this->data = [];

        for ($current = $start; $current <= $end; $current = $current->modify('+1 day')) {
            $this->downloadDay($query, $current);
        }
    }

    /**
     * @param Query $query
     * @param \DateTimeImmutable $day
     */
    protected function downloadDay(Query $query, \DateTimeImmutable $day): void
    {
        $secondInitial = 0;
        $secondEnd = 86399;
        $totalRecords = 0;

        $hasCallable = is_callable($this->onFiveHundred);

        while (true) {
            $list = $this->downloadSeconds($query, $day, $secondInitial, $secondEnd);
            $result = $list->count();

            if ($hasCallable && $result >= 500) {
                $params = [
                    'count' => $result,
                    'year' => $day->format('Y'),
                    'month' => $day->format('m'),
                    'day' => $day->format('d'),
                    'secondIni' => $secondInitial,
                    'secondFin' => $secondEnd,
                ];
                call_user_func($this->onFiveHundred, $params);
            }

            if ($result >= 500 && $secondEnd > $secondInitial) {
                $secondEnd = (int)floor($secondInitial + (($secondEnd - $secondInitial) / 2));
                continue;
            }

            $totalRecords = $totalRecords + $result;
            if ($secondEnd >= 86399) {
                break;
            }

            $secondInitial = $secondEnd + 1;
            $secondEnd = 86399;
        }
    }

    /**
     * @param Query $baseQuery
     * @param \DateTimeImmutable $day
     * @param $startSec
     * @param $endSec
     *
     * @return MetadataList
     */
    protected function downloadSeconds(Query $baseQuery, \DateTimeImmutable $day, int $startSec, int $endSec): MetadataList
    {
        $query = clone $baseQuery;

        $startDate = $query->getStartDate()
            ->setDate(
                (int)$day->format('Y'),
                (int)$day->format('m'),
                (int)$day->format('d')
            );

        if (0 !== $startSec) {
            $time = Helpers::converterSecondsToHours($startSec);
            [$startHour, $startMinute, $startSecond] = explode(':', $time);
            $startDate = $startDate->setTime((int)$startHour, (int)$startMinute, (int)$startSecond);
        }

        $query->setStartDate($startDate);

        $time = Helpers::converterSecondsToHours($endSec);

        [$endHour, $endMinute, $endSecond] = explode(':', $time);
        $endDate = $query->getEndDate()->setTime((int)$endHour, (int)$endMinute, (int)$endSecond);
        $query->setEndDate($endDate);

        $filters = $baseQuery->getDownloadType()->isEmitidos() ? new FiltersIssued($query) : new FiltersReceived($query);

        $html = $this->runQueryDate($query, $filters);
        $list = $this->makeData($html);

        return $list;
    }

    /**
     * @param Query $query
     * @param Filters $filters
     *
     * @return string
     */
    protected function runQueryDate(Query $query, Filters $filters): string
    {
        if ($query->getDownloadType()->isEmitidos()) {
            $url = URLS::SAT_URL_PORTAL_CFDI_CONSULTA_EMISOR;
            $result = $this->enterQueryTransmitter($filters);
        } else {
            $url = URLS::SAT_URL_PORTAL_CFDI_CONSULTA_RECEPTOR;
            $result = $this->enterQueryReceiver($filters);
        }

        $html = $result['html'];
        $inputs = $result['inputs'];

        $values = $this->getSearchValues($html, $inputs, $filters);

        $response = $this->client->post(
            $url,
            [
                'form_params' => $values,
                'headers' => Headers::postAjax(
                    URLS::SAT_HOST_PORTAL_CFDI,
                    $url
                ),
                'cookies' => $this->cookie,
                'future' => true,
                'verify' => false,
            ]
        );

        return $response->getBody()->getContents();
    }

    /**
     * @param Filters $filters
     *
     * @return array
     */
    protected function enterQueryReceiver(Filters $filters): array
    {
        $response = $this->client->get(
            URLS::SAT_URL_PORTAL_CFDI_CONSULTA_RECEPTOR,
            [
                'future' => true,
                'cookies' => $this->cookie,
                'verify' => false,
            ]
        );

        $html = $response->getBody()->getContents();

        $inputs = $this->parseInputs($html);
        $post = array_merge($inputs, $filters->getInitialFilters());

        $response = $this->client->post(
            URLS::SAT_URL_PORTAL_CFDI_CONSULTA_RECEPTOR,
            [
                'form_params' => $post,
                'headers' => Headers::postAjax(
                    URLS::SAT_HOST_PORTAL_CFDI,
                    URLS::SAT_URL_PORTAL_CFDI_CONSULTA_RECEPTOR
                ),
                'future' => true,
                'verify' => false,
                'cookies' => $this->cookie,
            ]
        );

        return [
            'html' => $response->getBody()->getContents(),
            'inputs' => $inputs,
        ];
    }

    /**
     * @param Filters $filters
     *
     * @return array
     */
    protected function enterQueryTransmitter(Filters $filters): array
    {
        $response = $this->client->get(
            URLS::SAT_URL_PORTAL_CFDI_CONSULTA_EMISOR,
            [
                'future' => true,
                'cookies' => $this->cookie,
                'verify' => false,
            ]
        );

        $html = $response->getBody()->getContents();

        $inputs = $this->parseInputs($html);
        $post = array_merge($inputs, $filters->getInitialFilters());

        $response = $this->client->post(
            URLS::SAT_URL_PORTAL_CFDI_CONSULTA_EMISOR,
            [
                'form_params' => $post,
                'headers' => Headers::postAjax(
                    URLS::SAT_HOST_PORTAL_CFDI,
                    URLS::SAT_URL_PORTAL_CFDI_CONSULTA_EMISOR
                ),
                'future' => true,
                'verify' => false,
                'cookies' => $this->cookie,
            ]
        );

        return [
            'html' => $response->getBody()->getContents(),
            'inputs' => $inputs,
        ];
    }

    /**
     * @param string $html
     * @param array $inputs
     * @param Filters $filters
     *
     * @return array
     */
    protected function getSearchValues($html, array $inputs, Filters $filters): array
    {
        $parser = new ParserFormatSAT($html);
        $valuesChange = $parser->getFormValues();
        $temporary = array_merge($inputs, $filters->getRequestFilters());
        $temp = array_merge($temporary, $valuesChange);

        return $temp;
    }

    /**
     * @param string $html
     *
     * @return array
     */
    protected function parseInputs($html): array
    {
        $htmlForm = new HtmlForm($html, 'form');
        $inputs = $htmlForm->getFormValues();

        return $inputs;
    }

    /**
     * @param string $html
     *
     * @return MetadataList
     */
    protected function makeData($html): MetadataList
    {
        $extractor = new MetadataExtractor();
        $data = $extractor->extract($html);
        $this->data = array_merge($this->data, $data);
        return new MetadataList($data);
    }

    /**
     * @return CookieJar
     */
    public function getCookie(): CookieJar
    {
        return $this->cookie;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return \Generator
     */
    public function getUrls(): \Generator
    {
        foreach ($this->getData() as $uuid => $data) {
            if (is_null($data['urlXml'])) {
                continue;
            }

            yield $data['urlXml'];
        }
    }

    public function getData(): MetadataList
    {
        return new MetadataList($this->data);
    }

    /**
     * @param callable $callback
     */
    public function setOnFiveHundred(callable $callback): void
    {
        $this->onFiveHundred = $callback;
    }

    /**
     * @return DownloadXML
     */
    public function downloader(): DownloadXML
    {
        return (new DownloadXML())
            ->setSatScraper($this);
    }
}
