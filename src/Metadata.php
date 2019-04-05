<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

class Metadata
{
    /** @var \stdClass */
    private $data;

    public function __construct(array $data)
    {
        $this->setDataFromArray($data);
    }

    public function __clone()
    {
        $this->data = clone $this->data;
        return $this;
    }

    public function get(string $keyword, string $default): string
    {
        return $this->data->{$keyword} ?? $default;
    }

    public function with(string $keyword, string $value): self
    {
        return $this->withData([$keyword => $value]);
    }

    public function withData(array $data): self
    {
        return (clone $this)->setDataFromArray($data);
    }

    private function setDataFromArray(array $data): self
    {
        foreach ($data as $key => $value) {
            $this->data->{$key} = strval($value);
        }
        return $this;
    }
}
