<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use PhpCfdi\CfdiSatScraper\Captcha\CaptchaBase64Extractor;
use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatSessionData;
use PhpCfdi\CfdiSatScraper\URLS;

/**
 * This class is an extraction for SatScraper authentication.
 * The entry point is SatSessionManager::initSession()
 *
 * @see SatSessionManager::initSession()
 * @internal
 */
class SatSessionManager
{
    /** @var SatSessionData */
    private $sessionData;

    /** @var SatHttpGateway */
    private $httpGateway;

    public function __construct(SatSessionData $sessionData, SatHttpGateway $httpGateway)
    {
        $this->httpGateway = $httpGateway;
        $this->sessionData = $sessionData;
    }

    /**
     * Initializates session on SAT, if it fails then will throw an exception
     * It only perform login if is not currently logged.
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

        if (false === strpos($htmlMainPage, 'RFC Autenticado: ' . $this->sessionData->getRfc())) {
            throw LoginException::notRegisteredAfterLogin($this->sessionData->getRfc(), $htmlMainPage); // 'The session is authenticated but main page does not contains your RFC'
        }
    }

    protected function requestCaptchaImage(): string
    {
        $html = $this->httpGateway->getAuthLoginPage(URLS::SAT_URL_LOGIN);
        $captchaBase64Extractor = new CaptchaBase64Extractor();
        $imageBase64 = $captchaBase64Extractor->retrieve($html);
        if ('' === $imageBase64) {
            throw LoginException::noCaptchaImageFound(URLS::SAT_URL_LOGIN, $html); // 'Unable to extract the base64 image from login page'
        }

        return $imageBase64;
    }

    protected function getCaptchaValue(int $attempt): string
    {
        $imageBase64 = $this->requestCaptchaImage();
        try {
            $resolver = $this->sessionData->getCaptchaResolver();
            $result = $resolver->decode($imageBase64);
            if ('' === $result) {
                throw LoginException::captchaWithoutAnswer($imageBase64, $resolver);
            }
            return $result;
        } catch (\Throwable $exception) {
            if ($attempt < $this->sessionData->getMaxTriesCaptcha()) {
                return $this->getCaptchaValue($attempt + 1);
            }

            throw $exception;
        }
    }

    protected function hasLogin(): bool
    {
        // check login on cfdiau
        $html = $this->httpGateway->getAuthLoginPage(URLS::SAT_URL_LOGIN);
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
            'Ecom_User_ID' => $this->sessionData->getRfc(),
            'Ecom_Password' => $this->sessionData->getCiec(),
            'option' => 'credential',
            'submit' => 'Enviar',
            'userCaptcha' => $captchaValue,
        ];
        $response = $this->httpGateway->postLoginData(URLS::SAT_URL_LOGIN, $loginData);

        if (false !== strpos($response, 'Ecom_User_ID')) {
            if ($attempt < $this->sessionData->getMaxTriesLogin()) {
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
}
