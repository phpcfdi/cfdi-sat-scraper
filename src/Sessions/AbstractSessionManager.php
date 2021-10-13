<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Sessions;

use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\Internal\HtmlForm;

abstract class AbstractSessionManager implements SessionManager
{
    abstract protected function createExceptionConnection(string $when, SatHttpGatewayException $exception): LoginException;

    abstract protected function createExceptionNotAuthenticated(string $html): LoginException;

    public function logout(): void
    {
        try {
            $this->getHttpGateway()->getLogout();
        } catch (SatHttpGatewayException $exception) {
            unset($exception);
        }
    }

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
            throw $this->createExceptionConnection('registering on login page', $exception);
        }

        if (false === strpos($htmlMainPage, 'RFC Autenticado: ' . $this->getRfc())) {
            // TODO: change to static method
            throw $this->createExceptionNotAuthenticated($htmlMainPage);
        }
    }
}
