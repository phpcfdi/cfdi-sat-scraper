<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\MetadataPackage;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use JsonSerializable;
use PhpCfdi\CfdiSatScraper\Exceptions\MetadataPackageException;

/**
 * This class stores the result of make a query to request a metadata package.
 * It contains the start date, end date, UUID (if any) and exception (if any).
 *
 * It cannot have UUID and exception at the same time.
 */
final readonly class QueryResult implements JsonSerializable
{
    /** @throws InvalidArgumentException when UUID is empty */
    public function __construct(
        private string $uuid,
        private DateTimeImmutable $startDate,
        private DateTimeImmutable $endDate,
        private ?MetadataPackageException $exception = null,
    ) {
        if ('' !== $this->uuid && null !== $this->exception) {
            throw new InvalidArgumentException('Cannot create with a UUID and Exception');
        }
    }

    public function hasUuid(): bool
    {
        return '' !== $this->uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function hasException(): bool
    {
        return null !== $this->exception;
    }

    public function getException(): ?Exception
    {
        return $this->exception;
    }

    /** @return array<string, string> */
    public function jsonSerialize(): array
    {
        return [
            'uuid' => $this->uuid,
            'startDate' => $this->startDate->format('Y-m-d H:i:s'),
            'endDate' => $this->endDate->format('Y-m-d H:i:s'),
            'exception' => (string) $this->exception?->getMessage(),
        ];
    }
}
