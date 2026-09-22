<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;

trait CommonMethodsScraperTrait
{
    private readonly SessionManager $sessionManager;

    private readonly SatHttpGateway $satHttpGateway;

    public function getSessionManager(): SessionManager
    {
        return $this->sessionManager;
    }

    public function getSatHttpGateway(): SatHttpGateway
    {
        return $this->satHttpGateway;
    }

    /**
     * Method factory to create a SatHttpGateway
     *
     * @internal
     */
    protected function createDefaultSatHttpGateway(): SatHttpGateway
    {
        return new SatHttpGateway();
    }

    public function confirmSessionIsAlive(): self
    {
        $sessionManager = $this->getSessionManager();
        $sessionManager->setHttpGateway($this->getSatHttpGateway());

        if (! $sessionManager->hasLogin()) {
            $sessionManager->login();
        }
        $sessionManager->accessPortalMainPage();

        return $this;
    }
}
