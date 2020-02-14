<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Captcha\CaptchaBase64Extractor;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\SATCredentialsException;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Internal\HtmlForm;

class SATScraper
{
    public const SAT_CREDENTIAL_ERROR = 'El RFC o CIEC son incorrectos';

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

    public function __construct(
        string $rfc,
        string $ciec,
        CaptchaResolverInterface $captchaResolver,
        ?SatHttpGateway $satHttpGateway = null
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
     * @return SATScraper
     *
     * @throws SATCredentialsException
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
            throw new \RuntimeException('The session is authenticated but main page does not contains your RFC');
        }
    }

    /**
     * This is only a consumption of the login page.
     * It is expected to return the raw html to be processed.
     *
     * Is known that if there is a session active it will return only a redirect page
     * with a known URL, but if it isn't, it will show a form to login.
     *
     * @return string
     * @throws \RuntimeException Unable to retrive the contents from login page
     */
    protected function consumeLoginPage(): string
    {
        return $this->satHttpGateway->getAuthLoginPage($this->loginUrl);
    }

    protected function requestCaptchaImage(): string
    {
        $html = $this->consumeLoginPage();
        $captchaBase64Extractor = new CaptchaBase64Extractor();
        $imageBase64 = $captchaBase64Extractor->retrieve($html);
        if ('' === $imageBase64) {
            throw new \RuntimeException('Unable to extract the base64 image from login page');
        }

        return $imageBase64;
    }

    protected function getCaptchaValue(int $attempt): string
    {
        $imageBase64 = $this->requestCaptchaImage();
        try {
            $result = $this->captchaResolver->decode($imageBase64);
            if ('' === $result) {
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

    protected function hasLogin(): bool
    {
        // check login on cfdiau
        $html = $this->consumeLoginPage();
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
        $response = $this->satHttpGateway->postLoginData($this->loginUrl, $this->rfc, $this->ciec, $this->getCaptchaValue(1));

        if (false !== strpos($response, 'Ecom_User_ID')) {
            if ($attempt < $this->maxTriesLogin) {
                return $this->login($attempt + 1);
            }

            throw new SATCredentialsException(self::SAT_CREDENTIAL_ERROR);
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

    public function downloadListUUID(array $uuids, DownloadTypesOption $downloadType): MetadataList
    {
        $this->initScraper();
        return $this->createMetadataDownloader()->downloadByUuids($uuids, $downloadType);
    }

    public function downloadPeriod(Query $query): MetadataList
    {
        $this->initScraper();
        return $this->createMetadataDownloader()->downloadByDate($query);
    }

    public function downloadByDateTime(Query $query): MetadataList
    {
        $this->initScraper();
        return $this->createMetadataDownloader()->downloadByDateTime($query);
    }

    public function downloader(?MetadataList $metadataList = null): DownloadXML
    {
        $downloadXml = new DownloadXML($this->satHttpGateway);
        if (null !== $metadataList) {
            $downloadXml->setMetadataList($metadataList);
        }
        return $downloadXml;
    }
}
