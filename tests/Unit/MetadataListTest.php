<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Metadata;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class MetadataListTest extends TestCase
{
    public function testCreateEmptyList(): Void
    {
        $list = new MetadataList([]);
        $this->assertCount(0, $list);
    }

    public function testHasMethod(): Void
    {
        $fakes = $this->fakes();
        $item = $fakes->doMetadata();
        $list = new MetadataList([$item]);
        $this->assertCount(1, $list);
        $this->assertTrue($list->has($item->uuid()));
        $this->assertFalse($list->has($fakes->faker()->uuid));
    }

    public function testFindMethod(): Void
    {
        $fakes = $this->fakes();
        $item = $fakes->doMetadata();
        $list = new MetadataList([$item]);
        $this->assertSame($item, $list->find($item->uuid()));
        $this->assertNull($list->find($fakes->faker()->uuid));
    }

    public function testGetMethod(): Void
    {
        $item = $this->fakes()->doMetadata();
        $list = new MetadataList([$item]);

        $this->assertSame($item, $list->get($item->uuid()));
    }

    public function testGetMethodWithoutUuid(): Void
    {
        $list = new MetadataList([]);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('UUID x-foo not found');
        $list->get('x-foo');
    }

    public function testIterateCollection(): void
    {
        $contents = $this->createMetadataArrayUsingUuids(...[
            $this->fakes()->faker()->uuid,
            $this->fakes()->faker()->uuid,
            $this->fakes()->faker()->uuid,
        ]);

        $list = new MetadataList($contents);
        $arrayList = [];
        foreach ($list as $uuid => $values) {
            $arrayList[$uuid] = $values;
        }
        $this->assertSame($contents, $arrayList);
    }

    public function testMerge(): void
    {
        $repeated = $this->fakes()->faker()->uuid;
        $first = new MetadataList($this->createMetadataArrayUsingUuids(...[
            $this->fakes()->faker()->uuid,
            $repeated,
            $this->fakes()->faker()->uuid,
        ]));
        $second = new MetadataList($this->createMetadataArrayUsingUuids(...[
            $this->fakes()->faker()->uuid,
            $repeated,
            $this->fakes()->faker()->uuid,
        ]));

        $merged = $first->merge($second);
        $this->assertNotSame($merged, $first);
        $this->assertCount(3, $first);
        $this->assertCount(3, $second);
        $this->assertCount(5, $merged);
    }

    public function testFilterWithUuids(): void
    {
        $uuids = [
            $uuid1 = $this->fakes()->faker()->uuid,
            $uuid2 = $this->fakes()->faker()->uuid,
            $uuid3 = $this->fakes()->faker()->uuid,
            $uuid4 = $this->fakes()->faker()->uuid,
        ];
        $uuidsFilter = [$uuid1, $uuid3];
        $list = new MetadataList($this->createMetadataArrayUsingUuids(...$uuids));
        $expectedFiltered = [
            $uuid1 => $list->get($uuid1),
            $uuid3 => $list->get($uuid3),
        ];

        $filtered = $list->filterWithUuids($uuidsFilter);

        $this->assertNotSame($filtered, $list, 'A new instance was expected');
        $this->assertTrue($filtered->has($uuid1), 'Expected filtered uuid was not found');
        $this->assertSame($filtered->get($uuid1), $list->get($uuid1), 'Items from the filtered list must be the same instace');
        $this->assertSame($expectedFiltered, iterator_to_array($filtered->getIterator()), 'List does not contains the same elements');
    }

    public function testFilterWithOutUuids(): void
    {
        $uuids = [
            $uuid1 = $this->fakes()->faker()->uuid,
            $uuid2 = $this->fakes()->faker()->uuid,
            $uuid3 = $this->fakes()->faker()->uuid,
            $uuid4 = $this->fakes()->faker()->uuid,
        ];
        $uuidsExclude = [$uuid1, $uuid3];
        $list = new MetadataList($this->createMetadataArrayUsingUuids(...$uuids));
        $expectedFiltered = [
            $uuid2 => $list->get($uuid2),
            $uuid4 => $list->get($uuid4),
        ];

        $filtered = $list->filterWithOutUuids($uuidsExclude);

        $this->assertNotSame($filtered, $list, 'A new instance was expected');
        $this->assertSame($expectedFiltered, iterator_to_array($filtered->getIterator()), 'List does not contains the same elements');
    }

    public function testOnlyContainsMetadataEvenWhenNullIsPassed(): void
    {
        /** @var Metadata[] $source */
        $source = [null];
        $list = new MetadataList($source);
        $this->assertCount(0, $list);
    }

    public function testCloningPreserveContentsAndUsesSameContentObjects(): void
    {
        $base = $this->fakes()->doMetadataList(10);
        $clon = clone $base;
        $this->assertEquals($base, $clon);
        $this->assertNotSame($base, $clon);
        foreach ($base as $baseItem) {
            $this->assertSame($baseItem, $clon->get($baseItem->uuid()));
        }
    }

    private function createMetadataArrayUsingUuids(string ...$uuids): array
    {
        $contents = array_map(function (string $uuid): Metadata {
            return new Metadata($uuid);
        }, $uuids);
        $contents = array_combine($uuids, $contents);
        return $contents ?: [];
    }
}
