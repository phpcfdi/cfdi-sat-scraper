<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

class MetadataListTest extends TestCase
{
    public function testCreateEmptyList(): Void
    {
        $list = new MetadataList([]);
        $this->assertCount(0, $list);
    }

    public function testHasMethod(): Void
    {
        $list = new MetadataList(['x-foo' => ['uuid' => 'x-foo']]);
        $this->assertCount(1, $list);
        $this->assertTrue($list->has('x-foo'));
        $this->assertFalse($list->has('x-bar'));
    }

    public function testFindMethod(): Void
    {
        $metadata = ['uuid' => 'x-foo'];
        $list = new MetadataList(['x-foo' => $metadata]);
        $this->assertSame($metadata, $list->find('x-foo'));
        $this->assertNull($list->find('x-bar'));
    }

    public function testGetMethod(): Void
    {
        $metadata = ['uuid' => 'x-foo'];
        $list = new MetadataList(['x-foo' => $metadata]);
        $this->assertSame($metadata, $list->get('x-foo'));
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
        $contents = [
            'x-1' => ['uuid' => 'x-1'],
            'x-2' => ['uuid' => 'x-2'],
            'x-3' => ['uuid' => 'x-3'],
        ];
        $list = new MetadataList($contents);
        $arrayList = [];
        foreach ($list as $uuid => $values) {
            $arrayList[$uuid] = $values;
        }
        $this->assertSame($contents, $arrayList);
    }

    public function testMerge(): void
    {
        $first = new MetadataList([
            'x-foo' => ['uuid' => 'x-foo'],
            'x-bar' => ['uuid' => 'x-bar'],
            'x-baz' => ['uuid' => 'x-baz'],
        ]);
        $second = new MetadataList([
            'x-xee' => ['uuid' => 'x-xee'],
            'x-bar' => ['uuid' => 'x-bar'],
            'x-zoo' => ['uuid' => 'x-zoo'],
        ]);

        $merged = $first->merge($second);
        $this->assertNotSame($merged, $first);
        $this->assertCount(3, $first);
        $this->assertCount(3, $second);
        $this->assertCount(5, $merged);
    }
}
