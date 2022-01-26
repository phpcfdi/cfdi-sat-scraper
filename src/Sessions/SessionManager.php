<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Sessions;

use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;

interface SessionManager
{
    /**
     * Check if the current session manager has an active session
     *
     * @throws LoginException
     */
    public function hasLogin(): bool;

    /**
     * Perform log in
     *
     * @throws LoginException
     */
    public function login(): void;

    /**
     * Perform log out
     */
    public function logout(): void;

    /**
     * Access to portal main page once session is created
     *
     * @throws LoginException
     */
    public function accessPortalMainPage(): void;

    /**
     * Get HTTP Gateway property
     * @throws LogicException when property has not been set
     */
    public function getHttpGateway(): SatHttpGateway;

    /**
     * Set HTTP Gateway property
     * @internal
     */
    public function setHttpGateway(SatHttpGateway $httpGateway): void;

    /**
     * Get the RFC associated with the session data
     *
     * @return string
     */
    public function getRfc(): string;
}
