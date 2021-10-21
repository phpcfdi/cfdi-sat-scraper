<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

use DateTimeImmutable;

interface MaximumRecordsHandler
{
    public function handle(DateTimeImmutable $moment): void;
}
