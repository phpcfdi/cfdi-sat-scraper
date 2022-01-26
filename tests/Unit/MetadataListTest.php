<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use JsonSerializable;
use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\Metadata;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class MetadataListTest extends TestCase
{
    public function testCreateEmptyList(): void
    {
        $list = new MetadataList([]);
        $this->assertCount(0, $list);
    }

    public function testHasMethod(): void
    {
        $fakes = $this->fakes();
        $item = $fakes->doMetadata();
        $list = new MetadataList([$item]);
        $this->assertCount(1, $list);
        $this->assertTrue($list->has($item->uuid()));
        $this->assertFalse($list->has($fakes->faker()->uuid));
    }

    public function testFindMethod(): void
    {
        $fakes = $this->fakes();
        $item = $fakes->doMetadata();
        $list = new MetadataList([$item]);
        $this->assertSame($item, $list->find($item->uuid()));
        $this->assertNull($list->find($fakes->faker()->uuid));
    }

    public function testGetMethod(): void
    {
        $item = $this->fakes()->doMetadata();
        $list = new MetadataList([$item]);

        $this->assertSame($item, $list->get($item->uuid()));
    }

    public function testGetMethodWithoutUuid(): void
    {
        $list = new MetadataList([]);
        $this->expectException(LogicException::class);
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
        $this->assertSame($contents, iterator_to_array($list));
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
            $uuid0 = $this->fakes()->faker()->uuid,
            $this->fakes()->faker()->uuid,
            $uuid2 = $this->fakes()->faker()->uuid,
        ];
        $uuidsFilter = [$uuid0, $uuid2];
        $list = new MetadataList($this->createMetadataArrayUsingUuids(...$uuids));
        $expectedFiltered = [
            $uuid0 => $list->get($uuid0),
            $uuid2 => $list->get($uuid2),
        ];

        $filtered = $list->filterWithUuids($uuidsFilter);

        $this->assertNotSame($filtered, $list, 'A new instance was expected');
        $this->assertTrue($filtered->has($uuid0), 'Expected filtered uuid was not found');
        $this->assertSame($filtered->get($uuid0), $list->get($uuid0), 'Items from the filtered list must be the same instace');
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

    public function testFilterWithResourceLink(): void
    {
        $faker = $this->fakes()->faker();
        $withXmlUrl = $this->createMetadataArrayUsingUuids(...[ // 3 items
            $faker->uuid,
            $faker->uuid,
            $faker->uuid,
        ]);
        $metadatas = $withXmlUrl + [  // + 2 items without url
            $uuid = $faker->uuid => new Metadata($uuid),
            $uuid = $faker->uuid => new Metadata($uuid),
        ];
        shuffle($metadatas);
        $metadataList = new MetadataList($metadatas);
        $filtered = $metadataList->filterWithResourceLink(ResourceType::xml());
        $this->assertEquals($withXmlUrl, iterator_to_array($filtered));
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

    public function testJsonSerializable(): void
    {
        $metadata = $this->fakes()->doMetadataList(10);
        $this->assertInstanceOf(JsonSerializable::class, $metadata);
        $this->assertSame($metadata->jsonSerialize(), iterator_to_array($metadata));
    }
}
