<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use Traversable;

/**
 * @implements \IteratorAggregate<Metadata>
 */
class MetadataList implements Countable, IteratorAggregate, JsonSerializable
{
    /** @var Metadata[] */
    private $list = [];

    /** @param Metadata[] $list */
    public function __construct(array $list)
    {
        $this->list = [];
        foreach ($list as $metadata) {
            if (! $metadata instanceof Metadata) {
                continue;
            }
            $this->list[$metadata->uuid()] = $metadata;
        }
    }

    public function merge(self $list): self
    {
        $new = new self([]);
        $new->list = array_merge($this->list, $list->list);
        return $new;
    }

    /**
     * Return a new list with only the Metadata that is on the uuids
     *
     * @param string[] $uuids
     * @return self
     */
    public function filterWithUuids(array $uuids): self
    {
        $uuids = array_change_key_case(array_flip($uuids), CASE_LOWER);
        return new self(array_intersect_key($this->list, $uuids));
    }

    /**
     * Return a new list excluding the Metadata that is on the uuids
     *
     * @param string[] $uuids
     * @return self
     */
    public function filterWithOutUuids(array $uuids): self
    {
        $uuids = array_change_key_case(array_flip($uuids), CASE_LOWER);
        return new self(array_diff_key($this->list, $uuids));
    }

    /**
     * Return a new list with only the Metadata wich has an url to download the corresponding XML
     *
     * @return self
     */
    public function filterWithDownloadLink(): self
    {
        return new self(array_filter($this->list, function (Metadata $metadata): bool {
            return $metadata->hasXmlDownloadUrl();
        }));
    }

    public function has(string $uuid): bool
    {
        return isset($this->list[strtolower($uuid)]);
    }

    /**
     * Retrieve a Metadata by UUID, if the metadata object does not exists returns NULL
     *
     * @param string $uuid
     * @return Metadata|null
     */
    public function find(string $uuid): ?Metadata
    {
        return $this->list[strtolower($uuid)] ?? null;
    }

    /**
     * Obtain a Metadata by UUID, the metadata object must exists in the collection
     *
     * @param string $uuid
     * @return Metadata
     * @throws LogicException when UUID is not found
     */
    public function get(string $uuid): Metadata
    {
        $values = $this->find($uuid);
        if (null === $values) {
            throw LogicException::generic("UUID $uuid not found");
        }
        return $values;
    }

    /**
     * @return Traversable|Metadata[]
     */
    public function getIterator()
    {
        return new ArrayIterator($this->list);
    }

    public function count(): int
    {
        return count($this->list);
    }

    /** @return array<string, Metadata> */
    public function jsonSerialize(): array
    {
        return $this->list;
    }
}
