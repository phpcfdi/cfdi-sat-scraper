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
    /** @var string */
    private $contents;

    public function __construct(string $message, string $contents, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->contents = $contents;
    }

    public function getContents(): string
    {
        return $this->contents;
    }
}
