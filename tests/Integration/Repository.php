<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Integration;

use ArrayIterator;
use Countable;
use DateTimeImmutable;
use IteratorAggregate;
use JsonSerializable;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use RuntimeException;
use Throwable;
use Traversable;

/**
 * Class Repository to be able to perform tests
 * @implements IteratorAggregate<RepositoryItem>
 */
class Repository implements Countable, IteratorAggregate, JsonSerializable
{
    /** @var array<string, RepositoryItem> */
    private $items;

    /** @param array<string, RepositoryItem> $items */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function fromFile(string $filename): self
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $content = strval(@file_get_contents($filename));
        $decoded = json_decode($content, true);
        try {
            return static::fromArray($decoded);
        } catch (Throwable $exception) {
            throw new RuntimeException('JSON decoded contents from %s are invalid', 0, $exception);
        }
    }

    /**
     * @param mixed|array<array{uuid: string, date: string, state: string, type: string}> $dataItems
     */
    public static function fromArray($dataItems): self
    {
        if (! is_array($dataItems)) {
            throw new RuntimeException('JSON decoded contents from %s is not an array');
        }
        $items = [];
        foreach ($dataItems as $index => $dataItem) {
            if (! is_array($dataItem)) {
                throw new RuntimeException("Entry $index is not an array");
            }
            $item = RepositoryItem::fromArray($dataItem);
            $items[$item->getUuid()] = $item;
        }
        return new self($items);
    }

    public function filterByState(StatesVoucherOption $state): self
    {
        if ($state->isTodos()) {
            return new self($this->items);
        }

        $itemState = $state->isCancelados() ? 'C' : 'V'; // C - cancelado, V - vigente
        return new self(
            array_filter(
                $this->items,
                function (RepositoryItem $item) use ($itemState): bool {
                    return $item->getState() == $itemState;
                },
            ),
        );
    }

    public function filterByType(DownloadType $type): self
    {
        $itemType = $type->isEmitidos() ? 'E' : 'R'; // E - emitido, R - recibido
        return new self(
            array_filter(
                $this->items,
                function (RepositoryItem $item) use ($itemType): bool {
                    return $item->getDownloadType() == $itemType;
                },
            ),
        );
    }

    public function randomize(): self
    {
        $items = $this->items;
        shuffle($items);
        return new self($items);
    }

    public function topItems(int $length): self
    {
        return new self(array_slice($this->items, 0, max(0, $length)));
    }

    /**
     * All the UUIDS collection into an array of strings
     *
     * @return string[]
     */
    public function getUuids(): array
    {
        return array_map(
            function (RepositoryItem $item): string {
                return $item->getUuid();
            },
            $this->items,
        );
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getSinceDate(): DateTimeImmutable
    {
        $date = null;
        foreach ($this->items as $item) {
            $date = min($item->getDate(), $date ?? $item->getDate());
        }
        if (null === $date) {
            throw new RuntimeException('It was not possible to get the *since* date');
        }
        return $date;
    }

    public function getUntilDate(): DateTimeImmutable
    {
        $date = null;
        foreach ($this->items as $item) {
            $date = max($item->getDate(), $date ?? $item->getDate());
        }
        if (null === $date) {
            throw new RuntimeException('It was not possible to get the *until* date');
        }
        return $date;
    }

    /** @return Traversable<string, RepositoryItem> */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /** @return RepositoryItem[] */
    public function jsonSerialize(): array
    {
        return iterator_to_array($this->getIterator());
    }
}
