<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use Throwable;

/**
 * The LoginException defines a problem on registering to the SAT platform with specific credentials.
 * It contains the SAT session data, retrieved contents and posted data.
 */
abstract class LoginException extends \RuntimeException implements SatException
{
    public function __construct(string $message, private readonly string $contents, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function getContents(): string
    {
        return $this->contents;
    }
}
