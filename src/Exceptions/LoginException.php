<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use RuntimeException as SplRuntimeException;

/**
 * The LoginException defines a problem on registering to the SAT platform with specific credentials.
 * It contains the SAT session data, retrieved contents and posted data.
 */
abstract class LoginException extends SplRuntimeException implements SatException
{
}
