<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

class MetadataList implements \Countable, \IteratorAggregate
{
    /**
     * @var array<string, array>
     */
    private $list = [];

    /**
     * @param array<string, array> $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }

    public function merge(MetadataList $list): MetadataList
    {
        return new MetadataList(array_merge($this->list, $list->list));
    }

    public function has(string $uuid): bool
    {
        return isset($this->list[$uuid]);
    }

    public function find(string $uuid): ?array
    {
        return $this->list[$uuid] ?? null;
    }

    public function get(string $uuid): array
    {
        $values = $this->find($uuid);
        if (null === $values) {
            throw new \RuntimeException(sprintf('UUID %s not found', $uuid));
        }
        return $values;
    }

    /**
     * @return \Traversable|array<string, array>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->list);
    }

    public function count(): int
    {
        return count($this->list);
    }
}
