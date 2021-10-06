<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Sessions;

use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;

interface SessionManager
{
    /**
     * @throws LoginException
     */
    public function hasLogin(): bool;

    /**
     * @throws LoginException
     */
    public function login(): void;

    public function logout(): void;

    /**
     * @throws LoginException
     */
    public function registerOnPortalMainPage(): void;

    public function getHttpGateway(): SatHttpGateway;

    public function setHttpGateway(SatHttpGateway $httpGateway): void;

    public function getRfc(): string;
}
