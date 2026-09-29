<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\MetadataPackage;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;

/**
 * Collection of available packages.
 * When iterated, the key is the package uuid but in lowercase.
 *
 * @implements IteratorAggregate<AvailablePackage>
 */
final class AvailablePackageList implements Countable, IteratorAggregate, JsonSerializable
{
    /** @var array<string, AvailablePackage> */
    private readonly array $list;

    /** @var array<string, string> */
    private array $baseFields = [];

    public function __construct(AvailablePackage ...$list)
    {
        $final = [];
        foreach ($list as $package) {
            $final[strtolower($package->getUuid())] = $package;
        }
        $this->list = $final;
    }

    /** @param array<string, string> $fields */
    public function withBaseFields(array $fields): self
    {
        $list = clone $this;
        $list->baseFields = $fields;
        return $list;
    }

    /** @return array<string, string> */
    public function getBaseFields(): array
    {
        return $this->baseFields;
    }

    public function has(string $uuid): bool
    {
        return isset($this->list[strtolower($uuid)]);
    }

    /**
     * Retrieve an AvailablePackage by UUID, if the package does not exist returns NULL
     */
    public function find(string $uuid): ?AvailablePackage
    {
        return $this->list[strtolower($uuid)] ?? null;
    }

    /**
     * Obtain an AvailablePackage by UUID, the package object must exist in the collection
     *
     * @throws LogicException when UUID is not found
     */
    public function get(string $uuid): AvailablePackage
    {
        $package = $this->find($uuid);
        if (null === $package) {
            throw LogicException::generic("UUID $uuid not found");
        }
        return $package;
    }

    /** @return ArrayIterator<string, AvailablePackage> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->list);
    }

    public function count(): int
    {
        return count($this->list);
    }

    /** @return array<string, AvailablePackage> */
    public function jsonSerialize(): array
    {
        return $this->list;
    }
}
