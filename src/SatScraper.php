<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Captcha\CaptchaBase64Extractor;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Internal\HtmlForm;
use PhpCfdi\CfdiSatScraper\Internal\MetadataDownloader;
use PhpCfdi\CfdiSatScraper\Internal\QueryResolver;

class SatScraper
{
    /** @var string */
    protected $rfc;

    /** @var string */
    protected $ciec;

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

    /** @var SatHttpGateway */
    private $satHttpGateway;

    /**
     * SatScraper constructor.
     *
     * @param string $rfc
     * @param string $ciec
     * @param CaptchaResolverInterface $captchaResolver
     * @param SatHttpGateway|null $satHttpGateway
     *
     * @throws InvalidArgumentException when RFC is an empty string
     * @throws InvalidArgumentException when CIEC is an empty string
     * @throws InvalidArgumentException when Login URL is not a valid url
     */
    public function __construct(
        string $rfc,
        string $ciec,
        CaptchaResolverInterface $captchaResolver,
        ?SatHttpGateway $satHttpGateway = null
    ) {
        if (empty($rfc)) {
            throw InvalidArgumentException::emptyInput('RFC');
        }

        if (empty($ciec)) {
            throw InvalidArgumentException::emptyInput('CIEC');
        }

        $this->rfc = $rfc;
        $this->ciec = $ciec;
        $this->loginUrl = URLS::SAT_URL_LOGIN;
        $this->satHttpGateway = $satHttpGateway ?? new SatHttpGateway();
        $this->captchaResolver = $captchaResolver;
    }

    public function getRfc(): string
    {
        return $this->rfc;
    }

    public function getLoginUrl(): string
    {
        return $this->loginUrl;
    }

    /**
     * Change the current Login URL to a new destination
     *
     * @param string $loginUrl
     * @return $this
     * @throws InvalidArgumentException when Login URL is not a valid url
     */
    public function setLoginUrl(string $loginUrl): self
    {
        if (! filter_var($loginUrl, FILTER_VALIDATE_URL)) {
            throw InvalidArgumentException::emptyInput('Login URL');
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

    public function getSatHttpGateway(): SatHttpGateway
    {
        return $this->satHttpGateway;
    }

    public function setSatHttpGateway(SatHttpGateway $satHttpGateway): self
    {
        $this->satHttpGateway = $satHttpGateway;

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
     * Initializates session on SAT
     *
     * @return SatScraper
     * @throws LoginException
     */
    public function initScraper(): self
    {
        if (! $this->hasLogin()) {
            $this->login(1);
        }
        $this->registerOnPortalMainPage();

        return $this;
    }

    protected function registerOnPortalMainPage(): void
    {
        $htmlMainPage = $this->satHttpGateway->getPortalMainPage();

        $inputs = (new HtmlForm($htmlMainPage, 'form'))->getFormValues();
        if (count($inputs) > 0) {
            $htmlMainPage = $this->satHttpGateway->postPortalMainPage($inputs);
        }

        if (false === strpos($htmlMainPage, 'RFC Autenticado: ' . $this->getRfc())) {
            throw LoginException::notRegisteredAfterLogin($this->rfc, $htmlMainPage); // 'The session is authenticated but main page does not contains your RFC'
        }
    }

    protected function requestCaptchaImage(): string
    {
        $html = $this->satHttpGateway->getAuthLoginPage($this->loginUrl);
        $captchaBase64Extractor = new CaptchaBase64Extractor();
        $imageBase64 = $captchaBase64Extractor->retrieve($html);
        if ('' === $imageBase64) {
            throw LoginException::noCaptchaImageFound($this->loginUrl, $html); // 'Unable to extract the base64 image from login page'
        }

        return $imageBase64;
    }

    protected function getCaptchaValue(int $attempt): string
    {
        $imageBase64 = $this->requestCaptchaImage();
        try {
            $result = $this->captchaResolver->decode($imageBase64);
            if ('' === $result) {
                throw LoginException::captchaWithoutAnswer($imageBase64, $this->captchaResolver);
            }
            return $result;
        } catch (\Throwable $exception) {
            if ($attempt < $this->maxTriesCaptcha) {
                return $this->getCaptchaValue($attempt + 1);
            }

            throw $exception;
        }
    }

    protected function hasLogin(): bool
    {
        // check login on cfdiau
        $html = $this->satHttpGateway->getAuthLoginPage($this->loginUrl);
        if (false === strpos($html, 'https://cfdiau.sat.gob.mx/nidp/app?sid=0')) {
            $this->logout();
            return  false;
        }

        // check main page
        $html = $this->satHttpGateway->getPortalMainPage();
        if (false !== strpos($html, urlencode('https://portalcfdi.facturaelectronica.sat.gob.mx/logout.aspx?salir=y'))) {
            $this->logout();
            return  false;
        }

        return true;
    }

    protected function login(int $attempt): string
    {
        $captchaValue = $this->getCaptchaValue(1);
        $loginData = [
            'Ecom_User_ID' => $this->rfc,
            'Ecom_Password' => $this->ciec,
            'option' => 'credential',
            'submit' => 'Enviar',
            'userCaptcha' => $captchaValue,
        ];
        $response = $this->satHttpGateway->postLoginData($this->loginUrl, $loginData);

        if (false !== strpos($response, 'Ecom_User_ID')) {
            if ($attempt < $this->maxTriesLogin) {
                return $this->login($attempt + 1);
            }

            throw LoginException::incorrectLoginData($loginData);
        }

        return $response;
    }

    protected function logout(): void
    {
        $this->satHttpGateway->getPortalPage('https://portalcfdi.facturaelectronica.sat.gob.mx/logout.aspx?salir=y');
        $this->satHttpGateway->getPortalPage('https://cfdiau.sat.gob.mx/nidp/app/logout?locale=es');
        $this->satHttpGateway->clearCookieJar();
    }

    public function createMetadataDownloader(): MetadataDownloader
    {
        return new MetadataDownloader(
            new QueryResolver($this->satHttpGateway),
            $this->onFiveHundred
        );
    }

    /**
     * Retrieve the MetadataList using specific UUIDS to download
     *
     * @param array $uuids
     * @param DownloadTypesOption $downloadType
     * @return MetadataList
     * @throws LoginException
     */
    public function downloadListUUID(array $uuids, DownloadTypesOption $downloadType): MetadataList
    {
        $this->initScraper();
        return $this->createMetadataDownloader()->downloadByUuids($uuids, $downloadType);
    }

    /**
     * Retrieve the MetadataList based on the query, but uses full days on dates (without time parts)
     *
     * @param Query $query
     * @return MetadataList
     * @throws LoginException
     */
    public function downloadPeriod(Query $query): MetadataList
    {
        $this->initScraper();
        return $this->createMetadataDownloader()->downloadByDate($query);
    }

    /**
     * Retrieve the MetadataList based on the query, but uses the period considering dates and times
     *
     * @param Query $query
     * @return MetadataList
     * @throws LoginException
     */
    public function downloadByDateTime(Query $query): MetadataList
    {
        $this->initScraper();
        return $this->createMetadataDownloader()->downloadByDateTime($query);
    }

    public function downloader(?MetadataList $metadataList = null): DownloadXml
    {
        return new DownloadXml($this->satHttpGateway, $metadataList);
    }
}
