<?php

/** @noinspection PhpMissingParentCallCommonInspection */

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Internal;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Contracts\MetadataMessageHandler;

/**
 * This class tracks every call to maximum records reached on one moment.
 * Is used for testing.
 */
final class MetadataMessageTracker implements MetadataMessageHandler
{
    /** @var string[] */
    private array $resolved = [];

    /** @var string[] */
    private array $resolvedDates = [];

    /** @var string[] */
    private array $divisions = [];

    /** @var string[] */
    private array $maximum = [];

    /** @return string[] */
    public function getResolved(): array
    {
        return $this->resolved;
    }

    /** @return string[] */
    public function getResolvedDates(): array
    {
        return $this->resolvedDates;
    }

    /** @return string[] */
    public function getDivisions(): array
    {
        return $this->divisions;
    }

    /** @return string[] */
    public function getMaximum(): array
    {
        return $this->maximum;
    }

    public function resolved(DateTimeImmutable $since, DateTimeImmutable $until, int $count): void
    {
        $this->resolved[] = sprintf(
            '%s - %s: %s',
            $since->format('Y-m-d H:i:s'),
            $until->format('Y-m-d H:i:s'),
            $count,
        );
    }

    public function date(DateTimeImmutable $since, DateTimeImmutable $until, int $count): void
    {
        $this->resolvedDates[] = sprintf(
            '%s - %s: %s',
            $since->format('Y-m-d H:i:s'),
            $until->format('Y-m-d H:i:s'),
            $count,
        );
    }

    public function divide(DateTimeImmutable $since, DateTimeImmutable $until): void
    {
        $this->divisions[] = sprintf('%s - %s', $since->format('Y-m-d H:i:s'), $until->format('Y-m-d H:i:s'));
    }

    public function maximum(DateTimeImmutable $moment): void
    {
        $this->maximum[] = $moment->format('Y-m-d H:i:s');
    }
}
