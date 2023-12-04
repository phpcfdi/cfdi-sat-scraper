<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

use DateTimeImmutable;

/**
 * @see MetadataMessageHandler
 * @deprecated 3.3.0
 */
interface MaximumRecordsHandler
{
    public function handle(DateTimeImmutable $moment): void;
}
