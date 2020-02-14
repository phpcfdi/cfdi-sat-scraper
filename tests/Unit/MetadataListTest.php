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
