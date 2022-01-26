<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Sessions\Ciec;

use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\Internal\CaptchaBase64Extractor;
use PhpCfdi\CfdiSatScraper\Sessions\AbstractSessionManager;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;
use PhpCfdi\CfdiSatScraper\URLS;
use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use Throwable;

final class CiecSessionManager extends AbstractSessionManager implements SessionManager
{
    /** @var CiecSessionData */
    private $sessionData;

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
    public function requestCaptchaImage(): CaptchaImage
    {
        try {
            $html = $this->getHttpGateway()->getAuthLoginPage(URLS::AUTH_LOGIN);
        } catch (SatHttpGatewayException $exception) {
            throw CiecLoginException::connectionException('getting captcha image', $this->sessionData, $exception);
        }

        try {
            $captchaBase64Extractor = new CaptchaBase64Extractor();
            $captchaImage = $captchaBase64Extractor->retrieveCaptchaImage($html);
        } catch (Throwable $exception) {
            throw CiecLoginException::noCaptchaImageFound($this->sessionData, $html, $exception);
        }

        return $captchaImage;
    }

    /**
     * @param int $attempt
     * @return string
     * @throws CiecLoginException
     */
    public function getCaptchaValue(int $attempt): string
    {
        $captchaImage = $this->requestCaptchaImage();
        try {
            $result = $this->sessionData->getCaptchaResolver()->resolve($captchaImage);
            return $result->getValue();
        } catch (Throwable $exception) {
            if ($attempt < $this->sessionData->getMaxTriesCaptcha()) {
                return $this->getCaptchaValue($attempt + 1);
            }
            if (! $exception instanceof CiecLoginException) {
                $exception = CiecLoginException::captchaWithoutAnswer($this->sessionData, $captchaImage, $exception);
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

        // check login on CFDIAU
        try {
            $html = $httpGateway->getAuthLoginPage(URLS::AUTH_LOGIN);
        } catch (SatHttpGatewayException $exception) {
            throw CiecLoginException::connectionException('getting login page', $this->sessionData, $exception);
        }
        // if the user has a valid session then CFDIAU will try to send to this location
        if (false === strpos($html, 'https://cfdiau.sat.gob.mx/nidp/app?sid=0')) {
            $this->logout();
            return false;
        }

        // check main page
        try {
            $html = $httpGateway->getPortalMainPage();
        } catch (SatHttpGatewayException $exception) {
            throw CiecLoginException::connectionException('getting portal main page', $this->sessionData, $exception);
        }
        // if portal main page session is no longer valid then will try to force you to log out
        if (false !== strpos($html, urlencode('https://portalcfdi.facturaelectronica.sat.gob.mx/logout.aspx?salir=y'))) {
            $this->logout();
            return false;
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
            $response = $this->getHttpGateway()->postLoginData(URLS::AUTH_LOGIN, $postData);
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

    public function getRfc(): string
    {
        return $this->sessionData->getRfc();
    }

    protected function createExceptionConnection(string $when, SatHttpGatewayException $exception): LoginException
    {
        return CiecLoginException::connectionException('registering on login page', $this->sessionData, $exception);
    }

    protected function createExceptionNotAuthenticated(string $html): LoginException
    {
        return CiecLoginException::notRegisteredAfterLogin($this->sessionData, $html);
    }
}
