<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Contracts\MaximumRecordsHandler;

/**
 * Null implementation of MaximumRecordsHandler.
 *
 * @internal
 */
final class NullMaximumRecordsHandler implements MaximumRecordsHandler
{
    public function handle(DateTimeImmutable $moment): void
    {
        // As a *null implementation** it must do nothing.
    }
}
