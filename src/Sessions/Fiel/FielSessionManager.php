<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Sessions\Fiel;

use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\Internal\HtmlForm;
use PhpCfdi\CfdiSatScraper\Sessions\AbstractSessionManager;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;
use PhpCfdi\CfdiSatScraper\URLS;
use PhpCfdi\Credentials\Credential;

final class FielSessionManager extends AbstractSessionManager implements SessionManager
{
    /** @var FielSessionData */
    private $sessionData;

    public function __construct(FielSessionData $fielSessionData)
    {
        $this->sessionData = $fielSessionData;
    }

    public static function create(Credential $credential): self
    {
        return new self(new FielSessionData($credential));
    }

    public function hasLogin(): bool
    {
        $httpGateway = $this->getHttpGateway();

        // if cookie is empty, then it will not be able to detect a session anyway
        if ($httpGateway->isCookieJarEmpty()) {
            return false;
        }

        try {
            // check is logged in on portal
            $html = $httpGateway->getPortalMainPage();
            if (false === strpos($html, 'RFC Autenticado: ' . $this->getRfc())) {
                return false;
            }
        } catch (SatHttpGatewayException $exception) {
            // if http error, consider without session
            return false;
        }

        return true;
    }

    public function login(): void
    {
        $httpGateway = $this->getHttpGateway();

        try {
            // contact homepage, it will try to redirect to access by password
            $httpGateway->getPortalMainPage();

            // previous page will try to redirect to access by password using post
            $httpGateway->postCiecLoginData(URLS::AUTH_LOGIN_CIEC, []);

            // change to fiel login page and get challenge
            $html = $httpGateway->getAuthLoginPage(URLS::AUTH_LOGIN_FIEL, URLS::AUTH_LOGIN_CIEC);

            // resolve and submit challenge, it returns an autosubmit form
            $inputs = $this->resolveChallengeUsingFiel($html);
            $html = $httpGateway->postFielLoginData(URLS::AUTH_LOGIN_FIEL, $inputs);

            // submit login credentials to portalcfdi
            $form = new HtmlForm($html, 'form');
            $inputs = $form->getFormValues(); // wa, weesult, wctx
            $httpGateway->postPortalMainPage($inputs);
        } catch (SatHttpGatewayException $exception) {
            throw FielLoginException::connectionException('try to login using FIEL', $this->sessionData, $exception);
        }
    }

    public function getSessionData(): FielSessionData
    {
        return $this->sessionData;
    }

    public function getRfc(): string
    {
        return $this->sessionData->getRfc();
    }

    /**
     * @param string $html
     * @return array<string, string>
     */
    private function resolveChallengeUsingFiel(string $html): array
    {
        $resolver = ChallengeResolver::createFromHtml($html, $this->getSessionData());
        return $resolver->obtainFormFields();
    }

    protected function createExceptionConnection(string $when, SatHttpGatewayException $exception): LoginException
    {
        return FielLoginException::connectionException($when, $this->sessionData, $exception);
    }

    protected function createExceptionNotAuthenticated(string $html): LoginException
    {
        return FielLoginException::notRegisteredAfterLogin($this->sessionData, $html);
    }
}
