<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\SATAuthenticatedException;
use PhpCfdi\CfdiSatScraper\Exceptions\SATCredentialsException;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use Symfony\Component\DomCrawler\Crawler;

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
     * @var callable|null
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
                URLS::SAT_URL_LOGIN,
                [
                    'future' => true,
                    'verify' => false,
                    'cookies' => $this->cookie,
                ]
            )->getBody()->getContents();
			
			$crawler = new Crawler($image);
			$imageBase64 = $crawler->filterXPath('//img')->attr('src');	

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
                    'userCaptcha' => $this->getCaptchaValue(),
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
     * @return MetadataList
     * @throws \Exception
     */
    public function downloadListUUID(array $uuids, DownloadTypesOption $downloadType): MetadataList
    {
        $query = new Query(new \DateTimeImmutable(), new \DateTimeImmutable());
        $query->setDownloadType($downloadType);

        $result = new MetadataList([]);
        foreach ($uuids as $uuid) {
            $query->setUuid([$uuid]);
            $uuidResult = $this->resolveQuery($query);
            $result = $result->merge($uuidResult);
        }
        return $result;
    }

    /**
     * @param Query $query
     * @return MetadataList
     * @throws SATAuthenticatedException
     * @throws SATCredentialsException
     */
    public function downloadPeriod(Query $query): MetadataList
    {
        $this->initScraper($query->getDownloadType());
        $start = $query->getStartDate()->setTime(0, 0, 0);
        $end = $query->getEndDate()->setTime(0, 0, 0);

        $result = new MetadataList([]);
        for ($current = $start; $current <= $end; $current = $current->modify('+1 day')) {
            $result = $result->merge($this->downloadDay($query, $current));
        }
        return $result;
    }

    /**
     * @param Query $query
     * @param \DateTimeImmutable $day
     * @return MetadataList
     */
    protected function downloadDay(Query $query, \DateTimeImmutable $day): MetadataList
    {
        $finalList = new MetadataList([]);
        $secondInitial = 0;
        $secondEnd = 86399;

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

            $finalList = $finalList->merge($list);
            if ($secondEnd >= 86399) {
                break;
            }

            $secondInitial = $secondEnd + 1;
            $secondEnd = 86399;
        }

        return $finalList;
    }

    /**
     * Download seconds must obtain the metadata list for one specific day with seconds interval
     *
     * @param Query $query Base query, all properties will be used but startDate and endDate
     * @param \DateTimeImmutable $day date to obtain, relevant parts are only year, month
     * @param int $startSec second of the day to start (min 0)
     * @param int $endSec second of the day to end (max 86399)
     *
     * @return MetadataList
     */
    protected function downloadSeconds(Query $query, \DateTimeImmutable $day, int $startSec, int $endSec): MetadataList
    {
        $query = clone $query;

        $time = Helpers::converterSecondsToHours($startSec);
        [$startHour, $startMinute, $startSecond] = explode(':', $time);
        $startDate = $day->setTime((int)$startHour, (int)$startMinute, (int)$startSecond);
        $query->setStartDate($startDate);

        $time = Helpers::converterSecondsToHours($endSec);
        [$endHour, $endMinute, $endSecond] = explode(':', $time);
        $endDate = $day->setTime((int)$endHour, (int)$endMinute, (int)$endSecond);
        $query->setEndDate($endDate);

        return $this->resolveQuery($query);
    }

    /**
     * @param Query $query
     *
     * @return MetadataList
     */
    protected function resolveQuery(Query $query): MetadataList
    {
        return (new QueryResolver($this->getClient(), $this->getCookie()))
            ->resolve($query);
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
        return new DownloadXML($this->getClient(), $this->getCookie());
    }
}
