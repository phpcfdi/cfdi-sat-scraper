<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use stdClass;

class Metadata
{
    /** @var stdClass */
    private $data;

    public function __construct(string $uuid, array $data = [])
    {
        $data = (object) $data;
        $this->data = $data;
        $this->data->uuid = $uuid;
    }

    public function uuid(): string
    {
        return $this->data->uuid;
    }

    public function get(string $key): string
    {
        return strval($this->data->{$key} ?? '');
    }

    public function has(string $key): bool
    {
        return isset($this->data->{$key});
    }
}
