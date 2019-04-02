<?php

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\SATAuthenticatedException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATCredentialsException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATException;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use Symfony\Component\DomCrawler\Crawler;
use PhpCfdi\CfdiSatScraper\Contracts\Filters;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Client;

/**
 * Class SATScraper.
 */
class SATScraper
{
    const SAT_CREDENTIAL_ERROR = 'El RFC o CIEC son incorrectos';

    /**
     * @var string
     */
    protected $rfc;

    /**
     * @var string
     */
    protected $ciec;

    /**
     * @var
     */
    protected $downloadType;

    /**
     * @var
     */
    protected $stateVoucher;

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
     * @param Options $options
     * @param Client $client
     * @param CookieJar $cookie
     */
    public function __construct(Options $options, Client $client, CookieJar $cookie)
    {
        $this->rfc = $options->getOption('rfc');
        $this->ciec = $options->getOption('ciec');
        $this->loginUrl = $options->getOption('loginUrl', URLS::SAT_URL_LOGIN);
        $this->client = $client;
        $this->cookie = $cookie;
    }

    /**
     * @param CaptchaResolverInterface $captchaResolver
     *
     * @return SATScraper
     */
    public function setCaptchaResolver(CaptchaResolverInterface $captchaResolver): SATScraper
    {
        $this->captchaResolver = $captchaResolver;

        return $this;
    }

    /**
     * @param string $downloadType
     *
     * @return SATScraper
     */
    public function setDownloadType(string $downloadType = DownloadType::RECEIVED): SATScraper
    {
        $this->downloadType = $downloadType;

        return $this;
    }

    /**
     * @param string $stateVoucher
     * @return SATScraper
     */
    public function setStateVoucher(string $stateVoucher): SATScraper
    {
        $this->stateVoucher = $stateVoucher;

        return $this;
    }

    /**
     * @param int $maxTriesCaptcha
     *
     * @return SATScraper
     */
    public function setMaxTriesCaptcha(int $maxTriesCaptcha): SATScraper
    {
        $this->maxTriesCaptcha = $maxTriesCaptcha;

        return $this;
    }

    /**
     * @param int $maxTriesLogin
     *
     * @return SATScraper
     */
    public function setMaxTriesLogin(int $maxTriesLogin): SATScraper
    {
        $this->maxTriesLogin = $maxTriesLogin;

        return $this;
    }

    /**
     * @return SATScraper
     * @throws SATAuthenticatedException
     * @throws SATCredentialsException
     */
    public function initScraper(): SATScraper
    {
        $this->login();
        $data = $this->dataAuth();
        $data = $this->postDataAuth($data);
        $data = $this->start($data);
        $this->selectType($data);

        return $this;
    }

    /**
     * @return string|null
     */
    protected function getCaptchaValue(): ?string
    {
        try {
            $image = $this->client->get(URLS::SAT_URL_CAPTCHA, [
                'future' => true,
                'verify' => false,
                'cookies' => $this->cookie
            ])->getBody()->getContents();

            $imageBase64 = base64_encode($image);

            return $this->captchaResolver
                ->setImage($imageBase64)
                ->decode();
        } catch (ConnectException $e) {
            if ($this->triesCaptcha < $this->maxTriesCaptcha) {
                $this->triesCaptcha++;
                return $this->getCaptchaValue();
            }

            throw new $e;
        }
    }

    /**
     * @return string
     * @throws SATCredentialsException
     */
    protected function login(): string
    {
        $response = $this->client->post($this->loginUrl, [
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
                'jcaptcha' => $this->getCaptchaValue()
            ],
        ])->getBody()->getContents();

        if (strpos($response, 'Ecom_User_ID') !== false) {
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
        $response = $this->client->get(URLS::SAT_URL_PORTAL_CFDI, [
            'future' => true,
            'cookies' => $this->cookie,
            'verify' => false,
        ])->getBody()->getContents();
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
            $response = $this->client->post(URLS::SAT_URL_PORTAL_CFDI, [
                'future' => true,
                'cookies' => $this->cookie,
                'verify' => false,
                'form_params' => $inputs,
            ])->getBody()->getContents();
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
        $response = $this->client->post(URLS::SAT_URL_PORTAL_CFDI, [
            'future' => true,
            'cookies' => $this->cookie,
            'verify' => false,
            'form_params' => $inputs,
        ])->getBody()->getContents();
        $inputs = $this->parseInputs($response);

        return $inputs;
    }

    /**
     * @param array $inputs
     *
     * @return string
     */
    protected function selectType(array $inputs): string
    {
        $data = [
            'ctl00$MainContent$TipoBusqueda' => $this->downloadType,
            '__ASYNCPOST' => 'true',
            '__EVENTTARGET' => '',
            '__EVENTARGUMENT' => '',
            'ctl00$ScriptManager1' => 'ctl00$MainContent$UpnlBusqueda|ctl00$MainContent$BtnBusqueda',
        ];

        $data = array_merge($inputs, $data);

        $response = $this->client->post(URLS::SAT_URL_PORTAL_CFDI_CONSULTA, [
            'future' => true,
            'cookies' => $this->cookie,
            'verify' => false,
            'form_params' => $data,
            'headers' => Headers::post(
                URLS::SAT_HOST_CFDI_AUTH,
                URLS::SAT_URL_PORTAL_CFDI
            ),
        ])->getBody()->getContents();

        return $response;
    }

    /**
     * @param array $uuids
     */
    public function downloadListUUID(array $uuids = []): void
    {
        $this->data = [];
        foreach ($uuids as $uuid) {
            $filters = new FiltersReceived();
            if ($this->downloadType == 'emitidos') {
                $filters = new FiltersIssued();
            }

            $filters->taxId = $uuid;

            $filters->stateVoucher = $this->stateVoucher;

            $html = $this->runQueryDate($filters);
            $this->makeData($html);
        }
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @throws SATAuthenticatedException
     * @throws SATCredentialsException
     * @throws SATException
     */
    public function downloadPeriod(\DateTime $start, \DateTime $end): void
    {
        $this->initScraper();
        $startParseDate = $start->format('Y-m-d');
        $endParseDate = $end->format('Y-m-d');

        if ($startParseDate <= $endParseDate) {
            $dateCurrent = strtotime($start->format('d') . '-' . $start->format('m') . '-' . $start->format('Y') . '00:00:00');
            $endDate = strtotime($end->format('d') . '-' . $end->format('m') . '-' . $end->format('Y') . '00:00:00');
            $this->data = [];

            while ($dateCurrent <= $endDate) {
                $day = new \DateTime(date('Y-m-d', $dateCurrent));
                $this->downloadDay($day);

                $dateCurrent1 = date('Y-m-d', $dateCurrent);
                $dateNow = strtotime('+1 day', strtotime($dateCurrent1));
                $dateCurrent = strtotime(date('Y-m-d', $dateNow));
            }
        } else {
            throw new SATException('Las fechas finales no pueden ser menores a las iniciales');
        }
    }

    /**
     * @param \DateTime $day
     */
    protected function downloadDay(\DateTime $day): void
    {
        $secondInitial = 1;
        $secondEnd = 86400;
        $queryStop = false;
        $totalRecords = 0;

        while ($queryStop === false) {
            $result = $this->downloadSeconds($day, (int)$secondInitial, (int)$secondEnd);

            if ($result >= 500 && !is_null($this->onFiveHundred) && is_callable($this->onFiveHundred)) {
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

            if ($result < 500 && $result !== '-1') {
                $totalRecords = (int)$totalRecords + $result;
                if ($secondEnd == 86400) {
                    $queryStop = true;
                }
                if ($secondEnd < 86400) {
                    $secondInitial = (int)$secondEnd + 1;
                    $secondEnd = 86400;
                }
            } else {
                if ($secondEnd > $secondInitial) {
                    $secondEnd = floor($secondInitial + (($secondEnd - $secondInitial) / 2));
                } elseif ($secondEnd <= $secondInitial) {
                    $totalRecords = (int)$totalRecords + $result;
                    if ($secondEnd == 86400) {
                        $queryStop = true;
                    } elseif ($secondEnd < 86400) {
                        $secondInitial = $secondEnd + 1;
                        $secondEnd = 86400;
                    }
                }
            }
        }
    }

    /**
     * @param \DateTime $day
     * @param $startSec
     * @param $endSec
     *
     * @return int
     */
    protected function downloadSeconds(\DateTime $day, $startSec, $endSec): int
    {
        $filters = new FiltersReceived();
        if ($this->downloadType == 'emitidos') {
            $filters = new FiltersIssued();
        }

        $filters->year = $day->format('Y');
        $filters->month = $day->format('m');
        $filters->day = $day->format('d');

        if ($startSec != '0') {
            $time = $filters->converterSecondsToHours($startSec);
            $time_start = explode(':', $time);
            $filters->hour_start = $time_start[0];
            $filters->minute_start = $time_start[1];
            $filters->second_start = $time_start[2];
        }

        $time = $filters->converterSecondsToHours($endSec);

        $time_end = explode(':', $time);
        $filters->hour_end = $time_end[0];
        $filters->minute_end = $time_end[1];
        $filters->second_end = $time_end[2];
        $filters->stateVoucher = $this->stateVoucher;

        $html = $this->runQueryDate($filters);
        $elements = $this->makeData($html);

        return $elements;
    }

    /**
     * @param Filters $filters
     *
     * @return string
     */
    protected function runQueryDate(Filters $filters): string
    {
        if ($this->downloadType == 'emitidos') {
            $url = URLS::SAT_URL_PORTAL_CFDI_CONSULTA_EMISOR;
            $result = $this->enterQueryTransmitter($filters);
        } else {
            $url = URLS::SAT_URL_PORTAL_CFDI_CONSULTA_RECEPTOR;
            $result = $this->enterQueryReceiver($filters);
        }

        $html = $result['html'];
        $inputs = $result['inputs'];

        $values = $this->getSearchValues($html, $inputs, $filters);

        $response = $this->client->post($url, [
            'form_params' => $values,
            'headers' => Headers::postAjax(
                URLS::SAT_HOST_PORTAL_CFDI,
                $url
            ),
            'cookies' => $this->cookie,
            'future' => true,
            'verify' => false,
        ]);

        return $response->getBody()->getContents();
    }

    /**
     * @param Filters $filters
     *
     * @return array
     */
    protected function enterQueryReceiver(Filters $filters): array
    {
        $response = $this->client->get(URLS::SAT_URL_PORTAL_CFDI_CONSULTA_RECEPTOR, [
            'future' => true,
            'cookies' => $this->cookie,
            'verify' => false,
        ]);

        $html = $response->getBody()->getContents();

        $inputs = $this->parseInputs($html);
        $post = array_merge($inputs, $filters->getFormPostDates());

        $response = $this->client->post(URLS::SAT_URL_PORTAL_CFDI_CONSULTA_RECEPTOR, [
            'form_params' => $post,
            'headers' => Headers::postAjax(
                URLS::SAT_HOST_PORTAL_CFDI,
                URLS::SAT_URL_PORTAL_CFDI_CONSULTA_RECEPTOR
            ),
            'future' => true,
            'verify' => false,
            'cookies' => $this->cookie,
        ]);

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
        $response = $this->client->get(URLS::SAT_URL_PORTAL_CFDI_CONSULTA_EMISOR, [
            'future' => true,
            'cookies' => $this->cookie,
            'verify' => false,
        ]);

        $html = $response->getBody()->getContents();

        $inputs = $this->parseInputs($html);
        $post = array_merge($inputs, $filters->getFormPostDates());

        $response = $this->client->post(URLS::SAT_URL_PORTAL_CFDI_CONSULTA_EMISOR, [
            'form_params' => $post,
            'headers' => Headers::postAjax(
                URLS::SAT_HOST_PORTAL_CFDI,
                URLS::SAT_URL_PORTAL_CFDI_CONSULTA_EMISOR
            ),
            'future' => true,
            'verify' => false,
            'cookies' => $this->cookie,
        ]);

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
        $temporary = array_merge($inputs, $filters->getPost());
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
     * @return int
     */
    protected function makeData($html): int
    {
        $crawler = new Crawler($html);

        $filteredElements = $crawler->filter('table#ctl00_MainContent_tblResult > tr')
            ->reduce(function (Crawler $node) {
                return $node->children()->first()->getNode(0)->tagName === 'td';
            });

        $numberOfElements = $filteredElements->count();

        if ($numberOfElements < 500) {
            $filteredElements->each(function (Crawler $node) {
                $tds = $node->filter('td[style="WORD-BREAK:BREAK-ALL;"]');

                $temp['uuid'] = trim($tds->getNode(0)->textContent);
                $temp['rfcEmisor'] = trim($tds->getNode(1)->textContent);
                $temp['nombreEmisor'] = trim($tds->getNode(2)->textContent);
                $temp['rfcReceptor'] = trim($tds->getNode(3)->textContent);
                $temp['nombreReceptor'] = trim($tds->getNode(4)->textContent);
                $temp['fechaEmision'] = trim($tds->getNode(5)->textContent);
                $temp['fechaCertificacion'] = trim($tds->getNode(6)->textContent);
                $temp['pacCertifico'] = trim($tds->getNode(7)->textContent);
                $temp['total'] = trim($tds->getNode(8)->textContent);
                $temp['efectoComprobante'] = trim($tds->getNode(9)->textContent);
                $temp['estatusCancelacion'] = trim($tds->getNode(10)->textContent);
                $temp['estadoComprobante'] = trim($tds->getNode(11)->textContent);
                $temp['estatusProcesoCancelacion'] = trim($tds->getNode(12)->textContent);
                $temp['fechaProcesoCancelacion'] = trim($tds->getNode(13)->textContent);
                $temp['fechaCancelacion'] = $temp['fechaProcesoCancelacion'];

                $onClickAttribute = $node->filter('span#BtnDescarga')->first()->attr('onclick');

                $temp['urlXml'] = str_replace(
                    ["return AccionCfdi('", "','Recuperacion');"],
                    [URLS::SAT_URL_PORTAL_CFDI, ''],
                    $onClickAttribute
                );
                $this->data[$temp['uuid']] = $temp;
            });
        }

        return $numberOfElements;
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
            yield $data['urlXml'];
        }
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
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
        return (new DownloadXML)
            ->setSatScraper($this);
    }

    public function __destruct()
    {
        $this->data = [];
    }
}
