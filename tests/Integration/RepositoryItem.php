<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Integration;

use DateTimeImmutable;
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

    public static function fromArray(array $item): self
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $dateTime = new DateTimeImmutable(strval($item['date'] ?? ''));
        return new self(
            strval($item['uuid'] ?? ''),
            $dateTime,
            strval($item['state'] ?? ''),
            strval($item['type'] ?? '')
        );
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

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
