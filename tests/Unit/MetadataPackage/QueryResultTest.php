<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\MetadataPackage;

use DateTimeImmutable;
use LogicException;
use PhpCfdi\CfdiSatScraper\Exceptions\MetadataPackageException;
use PhpCfdi\CfdiSatScraper\MetadataPackage\QueryResult;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class QueryResultTest extends TestCase
{
    public function testObjectWithUuid(): void
    {
        $object = new QueryResult(
            $uuid = 'b4fd0e43-4145-413b-a0fb-f6ce9b617e3c',
            $startDate = new DateTimeImmutable('2026-01-01 00:00:00'),
            $endDate = new DateTimeImmutable('2026-01-31 23:59:59'),
            $exception = null,
        );

        $this->assertTrue($object->hasUuid());
        $this->assertSame($uuid, $object->getUuid());
        $this->assertSame($startDate, $object->getStartDate());
        $this->assertSame($endDate, $object->getEndDate());
        $this->assertFalse($object->hasException());
        $this->assertSame($exception, $object->getException());
    }

    public function testObjectWithException(): void
    {
        $object = new QueryResult(
            $uuid = '',
            $startDate = new DateTimeImmutable('2026-01-01 00:00:00'),
            $endDate = new DateTimeImmutable('2026-01-31 23:59:59'),
            $exception = MetadataPackageException::searchHasNoResults(),
        );

        $this->assertFalse($object->hasUuid());
        $this->assertSame($uuid, $object->getUuid());
        $this->assertSame($startDate, $object->getStartDate());
        $this->assertSame($endDate, $object->getEndDate());
        $this->assertTrue($object->hasException());
        $this->assertSame($exception, $object->getException());
    }

    public function testConstructorWithUuidAndExceptionThrowsInvalidArgumentException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot create with a UUID and Exception');
        new QueryResult(
            'b4fd0e43-4145-413b-a0fb-f6ce9b617e3c',
            new DateTimeImmutable('2026-01-01 00:00:00'),
            new DateTimeImmutable('2026-01-31 23:59:59'),
            MetadataPackageException::searchHasNoResults(),
        );
    }

    public function testListIsIterableAndJsonSerializable(): void
    {
        $uuid = 'b4fd0e43-4145-413b-a0fb-f6ce9b617e3c';
        $end = '2026-01-31 23:59:59';
        $start = '2026-01-01 00:00:00';
        $expected = [
            'uuid' => $uuid,
            'startDate' => $start,
            'endDate' => $end,
            'exception' => '',
        ];
        /** @noinspection PhpUnhandledExceptionInspection */
        $object = new QueryResult(
            uuid: $uuid,
            startDate: new DateTimeImmutable($start),
            endDate: new DateTimeImmutable($end),
            exception: null,
        );

        $serialized = $object->jsonSerialize();

        $this->assertSame($expected, $serialized);
    }
}
