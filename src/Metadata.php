<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use InvalidArgumentException;

class Metadata
{
    /** @var array */
    private $data;

    public function __construct(string $uuid, array $data = [])
    {
        if ('' === $uuid) {
            throw new InvalidArgumentException('UUID cannot be empty');
        }
        $this->data = ['uuid' => strtolower($uuid)] + $data;
    }

    public function uuid(): string
    {
        return $this->data['uuid'];
    }

    public function get(string $key): string
    {
        return strval($this->data[$key] ?? '');
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }
}
