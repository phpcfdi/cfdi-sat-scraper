<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use PhpCfdi\CfdiSatScraper\Captcha\CaptchaBase64Extractor;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;

class SatSessionManager
{
    /** @var string */
    private $rfc;

    /** @var string */
    private $ciec;

    /** @var string */
    private $loginUrl;

    /** @var SatHttpGateway */
    private $httpGateway;

    /** @var CaptchaResolverInterface */
    private $captchaResolver;

    /** @var int */
    private $maxTriesCaptcha = 3;

    /** @var int */
    private $maxTriesLogin = 3;

    /**
     * SatSessionManager constructor.
     *
     * @param string $rfc
     * @param string $ciec
     * @param string $loginUrl
     * @param SatHttpGateway $httpGateway
     * @param CaptchaResolverInterface $captchaResolver
     * @param int $maxTriesCaptcha
     * @param int $maxTriesLogin
     * @throws InvalidArgumentException when RFC is an empty string
     * @throws InvalidArgumentException when CIEC is an empty string
     * @throws InvalidArgumentException when Login URL is not a valid url
     */
    public function __construct(
        string $rfc,
        string $ciec,
        string $loginUrl,
        SatHttpGateway $httpGateway,
        CaptchaResolverInterface $captchaResolver,
        int $maxTriesCaptcha,
        int $maxTriesLogin
    ) {
        $this->httpGateway = $httpGateway;

        $this->setRfc($rfc);
        $this->setCiec($ciec);
        $this->setLoginUrl($loginUrl);
        $this->setCaptchaResolver($captchaResolver);
        $this->setMaxTriesCaptcha($maxTriesCaptcha);
        $this->setMaxTriesLogin($maxTriesLogin);
    }

    /**
     * Initializates session on SAT
     *
     * @throws LoginException
     */
    public function initSession(): void
    {
        if (! $this->hasLogin()) {
            $this->login(1);
        }
        $this->registerOnPortalMainPage();
    }

    protected function registerOnPortalMainPage(): void
    {
        $htmlMainPage = $this->httpGateway->getPortalMainPage();

        $inputs = (new HtmlForm($htmlMainPage, 'form'))->getFormValues();
        if (count($inputs) > 0) {
            $htmlMainPage = $this->httpGateway->postPortalMainPage($inputs);
        }

        if (false === strpos($htmlMainPage, 'RFC Autenticado: ' . $this->rfc)) {
            throw LoginException::notRegisteredAfterLogin($this->rfc, $htmlMainPage); // 'The session is authenticated but main page does not contains your RFC'
        }
    }

    protected function requestCaptchaImage(): string
    {
        $html = $this->httpGateway->getAuthLoginPage($this->loginUrl);
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
            $resolver = $this->captchaResolver;
            $result = $resolver->decode($imageBase64);
            if ('' === $result) {
                throw LoginException::captchaWithoutAnswer($imageBase64, $resolver);
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
        $html = $this->httpGateway->getAuthLoginPage($this->loginUrl);
        if (false === strpos($html, 'https://cfdiau.sat.gob.mx/nidp/app?sid=0')) {
            $this->logout();
            return  false;
        }

        // check main page
        $html = $this->httpGateway->getPortalMainPage();
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
        $response = $this->httpGateway->postLoginData($this->loginUrl, $loginData);

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
        $this->httpGateway->getPortalPage('https://portalcfdi.facturaelectronica.sat.gob.mx/logout.aspx?salir=y');
        $this->httpGateway->getPortalPage('https://cfdiau.sat.gob.mx/nidp/app/logout?locale=es');
        $this->httpGateway->clearCookieJar();
    }

    public function getRfc(): string
    {
        return $this->rfc;
    }

    public function setRfc(string $rfc): void
    {
        if (empty($rfc)) {
            throw InvalidArgumentException::emptyInput('RFC');
        }
        $this->rfc = $rfc;
    }

    public function getCiec(): string
    {
        return $this->ciec;
    }

    public function setCiec(string $ciec): void
    {
        if (empty($ciec)) {
            throw InvalidArgumentException::emptyInput('CIEC');
        }
        $this->ciec = $ciec;
    }

    public function getLoginUrl(): string
    {
        return $this->loginUrl;
    }

    public function setLoginUrl(string $loginUrl): void
    {
        if (! filter_var($loginUrl, FILTER_VALIDATE_URL)) {
            throw InvalidArgumentException::emptyInput('Login URL');
        }
        $this->loginUrl = $loginUrl;
    }

    public function getCaptchaResolver(): CaptchaResolverInterface
    {
        return $this->captchaResolver;
    }

    public function setCaptchaResolver(CaptchaResolverInterface $captchaResolver): void
    {
        $this->captchaResolver = $captchaResolver;
    }

    public function getMaxTriesCaptcha(): int
    {
        return $this->maxTriesCaptcha;
    }

    public function setMaxTriesCaptcha(int $maxTriesCaptcha): void
    {
        $this->maxTriesCaptcha = $maxTriesCaptcha;
    }

    public function getMaxTriesLogin(): int
    {
        return $this->maxTriesLogin;
    }

    public function setMaxTriesLogin(int $maxTriesLogin): void
    {
        $this->maxTriesLogin = $maxTriesLogin;
    }
}
