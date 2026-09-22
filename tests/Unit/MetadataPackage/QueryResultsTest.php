<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\MetadataPackage;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\MetadataPackage\QueryResult;
use PhpCfdi\CfdiSatScraper\MetadataPackage\QueryResults;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class QueryResultsTest extends TestCase
{
    private function createPackage(string $uuid): QueryResult
    {
        return new QueryResult(
            $uuid,
            new DateTimeImmutable('2026-01-01 00:00:00'),
            new DateTimeImmutable('2026-01-31 23:59:59'),
        );
    }

    public function testListAccessors(): void
    {
        $package1 = $this->createPackage('FOO');
        $package2 = $this->createPackage('BAR');
        $list = new QueryResults($package1, $package2);

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
        $list = new QueryResults();
        $this->expectException(LogicException::class);
        $list->get('unknown-uuid');
    }

    public function testFilterWithUuid(): void
    {
        $list = new QueryResults(
            $this->createPackage('foo'),
            $this->createPackage(''),
            $this->createPackage('bar'),
        );

        $list = $list->filterWithUuid();

        $this->assertCount(2, $list);
        $this->assertTrue($list->has('foo'));
        $this->assertTrue($list->has('bar'));
        $this->assertFalse($list->has(''));
    }

    public function testFilterWithoutUuid(): void
    {
        $list = new QueryResults(
            $this->createPackage('foo'),
            $without = $this->createPackage(''),
            $this->createPackage('bar'),
        );

        $list = $list->filterWithoutUuid();

        $this->assertCount(1, $list);
        $this->assertFalse($list->has('foo'));
        $this->assertFalse($list->has('bar'));
        $this->assertSame([$without], iterator_to_array($list, preserve_keys: false));
    }

    public function testListIsIterable(): void
    {
        $packages = [
            $this->createPackage('aaa'),
            $this->createPackage('bbb'),
        ];
        $list = new QueryResults(...$packages);

        $this->assertSame($packages, iterator_to_array($list));
    }

    public function testListIsJsonSerializable(): void
    {
        $packages = [
            $this->createPackage('aaa'),
            $this->createPackage('bbb'),
        ];
        $list = new QueryResults(...$packages);

        $this->assertSame($packages, $list->jsonSerialize());
    }
}
