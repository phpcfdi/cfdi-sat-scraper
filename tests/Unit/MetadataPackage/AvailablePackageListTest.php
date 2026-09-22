<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\MetadataPackage;

use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\MetadataPackage\AvailablePackage;
use PhpCfdi\CfdiSatScraper\MetadataPackage\AvailablePackageList;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class AvailablePackageListTest extends TestCase
{
    public function testListAccessors(): void
    {
        $package1 = new AvailablePackage('FOO', 'blobUri');
        $package2 = new AvailablePackage('BAR', 'blobUri');
        $list = new AvailablePackageList($package1, $package2);

        $this->assertCount(2, $list);
        $this->assertTrue($list->has('foo'));
        $this->assertTrue($list->has('bar'));
        $this->assertFalse($list->has('xee'));
        $this->assertSame($package1, $list->find('foo'));
        $this->assertNull($list->find('xee'));
        $this->assertSame($package1, $list->get('foo'));
        $this->assertSame($package2, $list->get('bar'));
    }

    public function testListGetWithUnknownUuidThrowsException(): void
    {
        $list = new AvailablePackageList();
        $this->expectException(LogicException::class);
        $list->get('unknown-uuid');
    }

    public function testListIsIterable(): void
    {
        $expected = [
            'foo' => new AvailablePackage('FOO', 'blobUri?foo'),
            'bar' => new AvailablePackage('bar', 'blobUri?bar'),
        ];
        $list = new AvailablePackageList(...$expected);

        $this->assertSame($expected, iterator_to_array($list));
    }

    public function testListIsJsonSerializable(): void
    {
        $expected = [
            'foo' => new AvailablePackage('FOO', 'blobUri?foo'),
            'bar' => new AvailablePackage('bar', 'blobUri?bar'),
        ];
        $list = new AvailablePackageList(...$expected);

        $this->assertSame($expected, $list->jsonSerialize());
    }
}
