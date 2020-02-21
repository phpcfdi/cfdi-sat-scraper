<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use PhpCfdi\CfdiSatScraper\Captcha\CaptchaBase64Extractor;
use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatSessionData;
use PhpCfdi\CfdiSatScraper\URLS;
use Throwable;

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

    /**
     * @throws LoginException
     */
    public function registerOnPortalMainPage(): void
    {
        try {
            $htmlMainPage = $this->httpGateway->getPortalMainPage();
            $inputs = (new HtmlForm($htmlMainPage, 'form'))->getFormValues();
            if (count($inputs) > 0) {
                $htmlMainPage = $this->httpGateway->postPortalMainPage($inputs);
            }
        } catch (SatHttpGatewayException $exception) {
            throw LoginException::connectionException('registering on login page', $this->sessionData, $exception);
        }

        if (false === strpos($htmlMainPage, 'RFC Autenticado: ' . $this->sessionData->getRfc())) {
            throw LoginException::notRegisteredAfterLogin($this->sessionData, $htmlMainPage); // 'The session is authenticated but main page does not contains your RFC'
        }
    }

    /**
     * @return string
     * @throws LoginException
     */
    public function requestCaptchaImage(): string
    {
        try {
            $html = $this->httpGateway->getAuthLoginPage(URLS::SAT_URL_LOGIN);
        } catch (SatHttpGatewayException $exception) {
            throw LoginException::connectionException('getting captcha image', $this->sessionData, $exception);
        }
        $captchaBase64Extractor = new CaptchaBase64Extractor();
        $imageBase64 = $captchaBase64Extractor->retrieve($html);
        if ('' === $imageBase64) {
            throw LoginException::noCaptchaImageFound($this->sessionData, $html);
        }
        return $imageBase64;
    }

    /**
     * @param int $attempt
     * @return string
     * @throws LoginException
     */
    public function getCaptchaValue(int $attempt): string
    {
        $imageBase64 = $this->requestCaptchaImage();
        try {
            $result = $this->sessionData->getCaptchaResolver()->decode($imageBase64);
            if ('' === $result) {
                throw LoginException::captchaWithoutAnswer($this->sessionData, $imageBase64);
            }
            return $result;
        } catch (Throwable $exception) {
            if ($attempt < $this->sessionData->getMaxTriesCaptcha()) {
                return $this->getCaptchaValue($attempt + 1);
            }

            if (! $exception instanceof LoginException) {
                $exception = LoginException::captchaWithoutAnswer($this->sessionData, $imageBase64, $exception);
            }
            /** @var LoginException $exception */
            throw $exception;
        }
    }

    /**
     * @return bool
     * @throws LoginException
     */
    public function hasLogin(): bool
    {
        // check login on cfdiau
        try {
            $html = $this->httpGateway->getAuthLoginPage(URLS::SAT_URL_LOGIN);
        } catch (SatHttpGatewayException $exception) {
            throw LoginException::connectionException('getting login page', $this->sessionData, $exception);
        }
        if (false === strpos($html, 'https://cfdiau.sat.gob.mx/nidp/app?sid=0')) {
            $this->logout();
            return  false;
        }

        // check main page
        try {
            $html = $this->httpGateway->getPortalMainPage();
        } catch (SatHttpGatewayException $exception) {
            throw LoginException::connectionException('getting portal main page', $this->sessionData, $exception);
        }
        if (false !== strpos($html, urlencode('https://portalcfdi.facturaelectronica.sat.gob.mx/logout.aspx?salir=y'))) {
            $this->logout();
            return  false;
        }

        return true;
    }

    /**
     * @param int $attempt
     * @return string
     * @throws LoginException
     */
    public function login(int $attempt): string
    {
        $captchaValue = $this->getCaptchaValue(1);
        $postData = [
            'Ecom_User_ID' => $this->sessionData->getRfc(),
            'Ecom_Password' => $this->sessionData->getCiec(),
            'option' => 'credential',
            'submit' => 'Enviar',
            'userCaptcha' => $captchaValue,
        ];
        try {
            $response = $this->httpGateway->postLoginData(URLS::SAT_URL_LOGIN, $postData);
        } catch (SatHttpGatewayException $exception) {
            throw LoginException::connectionException('sending login data', $this->sessionData, $exception);
        }

        if (false !== strpos($response, 'Ecom_User_ID')) {
            if ($attempt < $this->sessionData->getMaxTriesLogin()) {
                return $this->login($attempt + 1);
            }

            throw LoginException::incorrectLoginData($this->sessionData, $response, $postData);
        }

        return $response;
    }

    /**
     * @throws LoginException
     */
    public function logout(): void
    {
        // TODO: if cookie is removed, is there any need to make logout process??
        try {
            $this->httpGateway->getPortalPage('https://portalcfdi.facturaelectronica.sat.gob.mx/logout.aspx?salir=y');
            $this->httpGateway->getPortalPage('https://cfdiau.sat.gob.mx/nidp/app/logout?locale=es');
        } catch (SatHttpGatewayException $exception) {
            throw LoginException::connectionException('closing session', $this->sessionData, $exception);
        } finally {
            $this->httpGateway->clearCookieJar();
        }
    }

    public function getSessionData(): SatSessionData
    {
        return $this->sessionData;
    }

    public function getHttpGateway(): SatHttpGateway
    {
        return $this->httpGateway;
    }
}
