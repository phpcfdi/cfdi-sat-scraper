<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Internal;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Contracts\MaximumRecordsHandler;

/**
 * This class tracks every call to maximum records reached on one moment.
 * Is used for testing.
 */
final class MaximumRecordsTracker implements MaximumRecordsHandler
{
    /** @var string[] */
    private $moments = [];

    public function handle(DateTimeImmutable $moment): void
    {
        $this->moments[] = $moment->format('Y-m-d H:i:s');
    }

    /** @return string[] */
    public function getMoments(): array
    {
        return $this->moments;
    }
}
