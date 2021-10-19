<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Sessions\Fiel;

use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use Throwable;

final class FielLoginException extends LoginException
{
    /** @var FielSessionData */
    private $sessionData;

    public function __construct(string $message, string $contents, FielSessionData $sessionData, Throwable $previous = null)
    {
        parent::__construct($message, $contents, $previous);
        $this->sessionData = $sessionData;
    }

    public static function connectionException(string $when, FielSessionData $sessionData, Throwable $previous = null): self
    {
        return new self("Connection error when $when", '', $sessionData, $previous);
    }

    public static function notRegisteredAfterLogin(FielSessionData $data, string $contents): self
    {
        $message = "It was expected to have the session registered on portal home page with RFC {$data->getRfc()}";
        return new self($message, $contents, $data);
    }

    public function getSessionData(): FielSessionData
    {
        return $this->sessionData;
    }
}
