<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Sessions\Ciec;

use LogicException;
use PhpCfdi\CfdiSatScraper\Captcha\CaptchaBase64Extractor;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\Sessions\AbstractSessionManager;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;
use PhpCfdi\CfdiSatScraper\URLS;
use Throwable;

final class CiecSessionManager extends AbstractSessionManager implements SessionManager
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

        // if cookie is empty, then it will not be able to detect a session anyway
        if ($httpGateway->isCookieJarEmpty()) {
            return false;
        }

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
    private function loginInternal(int $attempt): string
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

    protected function createExceptionConnection(string $when, SatHttpGatewayException $exception): LoginException
    {
        return CiecLoginException::connectionException('registering on login page', $this->sessionData, $exception);
    }

    public function createExceptionNotAuthenticated(string $html): LoginException
    {
        return CiecLoginException::notRegisteredAfterLogin($this->sessionData, $html);
    }
}
