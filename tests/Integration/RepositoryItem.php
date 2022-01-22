<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Integration;

use DateTimeImmutable;
use DomainException;
use Exception;
use JsonSerializable;

class RepositoryItem implements JsonSerializable
{
    /** @var string */
    private $uuid;

    /** @var DateTimeImmutable */
    private $date;

    /** @var string */
    private $type;

    /** @var string */
    private $state;

    public function __construct(string $uuid, DateTimeImmutable $date, string $state, string $type)
    {
        $this->uuid = strtolower($uuid);
        $this->date = $date;
        $this->type = strtoupper(substr($type, 0, 1));
        $this->state = strtoupper(substr($state, 0, 1));
    }

    /** @param array<mixed> $item */
    public static function fromArray(array $item): self
    {
        return new self(
            strval($item['uuid'] ?? ''),
            self::dateFromString(strval($item['date'] ?? '')),
            strval($item['state'] ?? ''),
            strval($item['type'] ?? ''),
        );
    }

    private static function dateFromString(string $value): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (Exception $exception) {
            throw new DomainException(sprintf('Unable to parse date with value %s', $value));
        }
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getDownloadType(): string
    {
        return $this->type;
    }

    public function getState(): string
    {
        return $this->state;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
