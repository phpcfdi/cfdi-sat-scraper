<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Contracts\MaximumRecordsHandler;

final class NullMaximumRecordsHandler implements MaximumRecordsHandler
{
    public function handle(DateTimeImmutable $moment): void
    {
    }
}
