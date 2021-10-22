<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Sessions;

use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\Internal\HtmlForm;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;

abstract class AbstractSessionManager implements SessionManager
{
    /** @var SatHttpGateway|null */
    private $httpGateway;

    abstract protected function createExceptionConnection(string $when, SatHttpGatewayException $exception): LoginException;

    abstract protected function createExceptionNotAuthenticated(string $html): LoginException;

    public function logout(): void
    {
        $this->getHttpGateway()->getLogout();
    }

    public function accessPortalMainPage(): void
    {
        $satHttpGateway = $this->getHttpGateway();
        try {
            $htmlMainPage = $satHttpGateway->getPortalMainPage();
            $inputs = (new HtmlForm($htmlMainPage, 'form'))->getFormValues();
            if (count($inputs) > 0) {
                $htmlMainPage = $satHttpGateway->postPortalMainPage($inputs);
            }
        } catch (SatHttpGatewayException $exception) {
            throw $this->createExceptionConnection('registering on login page', $exception);
        }

        if (false === strpos($htmlMainPage, 'RFC Autenticado: ' . $this->getRfc())) {
            throw $this->createExceptionNotAuthenticated($htmlMainPage);
        }
    }

    public function getHttpGateway(): SatHttpGateway
    {
        if (null === $this->httpGateway) {
            throw LogicException::generic('Must set http gateway property before use');
        }
        return $this->httpGateway;
    }

    public function setHttpGateway(SatHttpGateway $httpGateway): void
    {
        $this->httpGateway = $httpGateway;
    }
}
