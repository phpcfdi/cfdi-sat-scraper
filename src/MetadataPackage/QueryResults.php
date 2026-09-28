<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\MetadataPackage;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use Traversable;

/**
 * Collection of requested packages by period
 *
 * @implements IteratorAggregate<QueryResult>
 */
final readonly class QueryResults implements Countable, IteratorAggregate, JsonSerializable
{
    /** @var list<QueryResult> */
    private array $items;

    public function __construct(QueryResult ...$items)
    {
        $this->items = array_values($items);
    }

    public function has(string $uuid): bool
    {
        return null !== $this->find($uuid);
    }

    /**
     * Retrieve by UUID, if the package does not exist returns NULL
     */
    public function find(string $uuid): ?QueryResult
    {
        foreach ($this->items as $item) {
            if (0 === strcasecmp($item->getUuid(), $uuid)) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Create a new object only with elements that have UUID
     */
    public function filterWithUuid(): self
    {
        $items = array_filter(
            $this->items,
            static fn (QueryResult $item): bool => $item->hasUuid(),
        );
        return new self(...$items);
    }

    /**
     * Create a new object only with elements that does not have UUID
     */
    public function filterWithoutUuid(): self
    {
        $items = array_filter(
            $this->items,
            static fn (QueryResult $item): bool => ! $item->hasUuid(),
        );
        return new self(...$items);
    }

    /**
     * Retrieve by UUID, if the package does not exist throws exception
     *
     * @throws LogicException when UUID is not found
     */
    public function get(string $uuid): QueryResult
    {
        $item = $this->find($uuid);
        if (null === $item) {
            throw LogicException::generic("UUID $uuid not found");
        }
        return $item;
    }

    /** @return Traversable<int, QueryResult> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /** @return array<int, QueryResult> */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
