<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Integration;

use DateTimeImmutable;
use DomainException;
use Exception;

class RepositoryItemFactory
{
    /** @param array<mixed> $values */
    public function make(array $values): RepositoryItem
    {
        $maker = new self();
        return new RepositoryItem(
            $maker->stringFromValues($values, 'uuid'),
            $maker->dateFromString($maker->stringFromValues($values, 'date')),
            $maker->stringFromValues($values, 'state'),
            $maker->stringFromValues($values, 'type'),
        );
    }

    /** @param array<mixed> $values */
    private function stringFromValues(array $values, string $key): string
    {
        if (! isset($values[$key]) || ! is_string($values[$key])) {
            throw new DomainException(sprintf('Cannot create an entry with invalid %s', $key));
        }
        return $values[$key];
    }

    private function dateFromString(string $value): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (Exception) {
            throw new DomainException(sprintf('Unable to parse date with value %s', $value));
        }
    }
}
