<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 */

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
use Traversable;

/**
 * Class Repository to be able to perform tests
 * @implements IteratorAggregate<RepositoryItem>
 */
class Repository implements Countable, IteratorAggregate, JsonSerializable
{
    /** @var RepositoryItem[] */
    private $items;

    /** @param RepositoryItem[] $items */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function fromFile(string $filename): self
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $content = strval(@file_get_contents($filename));
        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('JSON decoded contents from %s is not an array');
        }
        return static::fromArray($decoded);
    }

    /**
     * @param array<array<string, string>> $dataItems
     * @return self
     * @throws \Exception
     */
    public static function fromArray(array $dataItems): self
    {
        $items = [];
        foreach ($dataItems as $dataItem) {
            $items[] = RepositoryItem::fromArray($dataItem);
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
                }
            )
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
                }
            )
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
            $this->items
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

    /** @return Traversable<RepositoryItem> */
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
