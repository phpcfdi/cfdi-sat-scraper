<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Sessions;

use LogicException;
use PhpCfdi\CfdiSatScraper\Captcha\CaptchaBase64Extractor;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\CiecLoginException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\Internal\HtmlForm;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\URLS;
use Throwable;

final class CiecSessionManager implements SessionManager
{
    /** @var CiecSessionData */
    private $sessionData;

    /** @var SatHttpGateway|null */
    private $httpGateway;

    public function __construct(CiecSessionData $sessionData)
    {
        $this->sessionData = $sessionData;
    }

    public static function create(string $rfc, string $ciec, CaptchaResolverInterface $resolver): self
    {
        $sessionData = new CiecSessionData($rfc, $ciec, $resolver);
        return new self($sessionData);
    }

    /**
     * @throws CiecLoginException
     */
    public function registerOnPortalMainPage(): void
    {
        $satHttpGateway = $this->getHttpGateway();
        try {
            $htmlMainPage = $satHttpGateway->getPortalMainPage();
            $inputs = (new HtmlForm($htmlMainPage, 'form'))->getFormValues();
            if (count($inputs) > 0) {
                $htmlMainPage = $satHttpGateway->postPortalMainPage($inputs);
            }
        } catch (SatHttpGatewayException $exception) {
            throw CiecLoginException::connectionException('registering on login page', $this->sessionData, $exception);
        }

        if (false === strpos($htmlMainPage, 'RFC Autenticado: ' . $this->sessionData->getRfc())) {
            throw CiecLoginException::notRegisteredAfterLogin($this->sessionData, $htmlMainPage); // 'The session is authenticated but main page does not contains your RFC'
        }
    }

    /**
     * @return string
     * @throws CiecLoginException
     */
    public function requestCaptchaImage(): string
    {
        try {
            $html = $this->getHttpGateway()->getAuthLoginPage(URLS::SAT_URL_LOGIN);
        } catch (SatHttpGatewayException $exception) {
            throw CiecLoginException::connectionException('getting captcha image', $this->sessionData, $exception);
        }
        $captchaBase64Extractor = new CaptchaBase64Extractor();
        $imageBase64 = $captchaBase64Extractor->retrieve($html);
        if ('' === $imageBase64) {
            throw CiecLoginException::noCaptchaImageFound($this->sessionData, $html);
        }
        return $imageBase64;
    }

    /**
     * @param int $attempt
     * @return string
     * @throws CiecLoginException
     */
    public function getCaptchaValue(int $attempt): string
    {
        $imageBase64 = $this->requestCaptchaImage();
        try {
            $result = $this->sessionData->getCaptchaResolver()->decode($imageBase64);
            if ('' === $result) {
                throw CiecLoginException::captchaWithoutAnswer($this->sessionData, $imageBase64);
            }
            return $result;
        } catch (Throwable $exception) {
            if ($attempt < $this->sessionData->getMaxTriesCaptcha()) {
                return $this->getCaptchaValue($attempt + 1);
            }

            if (! $exception instanceof CiecLoginException) {
                $exception = CiecLoginException::captchaWithoutAnswer($this->sessionData, $imageBase64, $exception);
            }
            /** @var CiecLoginException $exception */
            throw $exception;
        }
    }

    public function hasLogin(): bool
    {
        $httpGateway = $this->getHttpGateway();
        // check login on cfdiau
        try {
            $html = $httpGateway->getAuthLoginPage(URLS::SAT_URL_LOGIN);
        } catch (SatHttpGatewayException $exception) {
            throw CiecLoginException::connectionException('getting login page', $this->sessionData, $exception);
        }
        if (false === strpos($html, 'https://cfdiau.sat.gob.mx/nidp/app?sid=0')) {
            $this->logout();
            return  false;
        }

        // check main page
        try {
            $html = $httpGateway->getPortalMainPage();
        } catch (SatHttpGatewayException $exception) {
            throw CiecLoginException::connectionException('getting portal main page', $this->sessionData, $exception);
        }
        if (false !== strpos($html, urlencode('https://portalcfdi.facturaelectronica.sat.gob.mx/logout.aspx?salir=y'))) {
            $this->logout();
            return  false;
        }

        return true;
    }

    public function login(): void
    {
        $this->loginInternal(1);
    }

    /**
     * @param int $attempt
     * @return string
     * @throws CiecLoginException
     */
    public function loginInternal(int $attempt): string
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
            $response = $this->getHttpGateway()->postLoginData(URLS::SAT_URL_LOGIN, $postData);
        } catch (SatHttpGatewayException $exception) {
            throw CiecLoginException::connectionException('sending login data', $this->sessionData, $exception);
        }

        if (false !== strpos($response, 'Ecom_User_ID')) {
            if ($attempt < $this->sessionData->getMaxTriesLogin()) {
                return $this->loginInternal($attempt + 1);
            }

            throw CiecLoginException::incorrectLoginData($this->sessionData, $response, $postData);
        }

        return $response;
    }

    public function logout(): void
    {
        // there is no need to touch logout urls, clearing the cookie jar must be enought
        $this->getHttpGateway()->clearCookieJar();
    }

    public function getSessionData(): CiecSessionData
    {
        return $this->sessionData;
    }

    public function getHttpGateway(): SatHttpGateway
    {
        if (null === $this->httpGateway) {
            throw new LogicException('Must set http gateway property before use');
        }
        return $this->httpGateway;
    }

    public function setHttpGateway(SatHttpGateway $httpGateway): void
    {
        $this->httpGateway = $httpGateway;
    }

    public function getRfc(): string
    {
        return $this->sessionData->getRfc();
    }
}
