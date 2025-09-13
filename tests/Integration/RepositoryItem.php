<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Integration;

use DateTimeImmutable;
use JsonSerializable;

class RepositoryItem implements JsonSerializable
{
    private string $uuid;

    private DateTimeImmutable $date;

    private string $type;

    private string $state;

    public function __construct(string $uuid, DateTimeImmutable $date, string $state, string $type)
    {
        $this->uuid = strtolower($uuid);
        $this->date = $date;
        $this->type = strtoupper(substr($type, 0, 1));
        $this->state = strtoupper(substr($state, 0, 1));
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
