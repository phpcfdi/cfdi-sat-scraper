<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Internal;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Internal\MetadataPackageQuerySplitter;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class MetadataPackageQuerySplitterTest extends TestCase
{
    public function testSplitWithReceivedMultiMonth(): void
    {
        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-30 01:00:00'),
            new DateTimeImmutable('2026-03-02 02:59:59'),
            DownloadType::recibidos(),
        );
        $splitter = new MetadataPackageQuerySplitter($query);

        $split = iterator_to_array($splitter->split());

        $this->assertCount(5, $split);
        $this->assertSame('2026-01-30 01:00:00', $split[0]->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-01-30 23:59:59', $split[0]->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-01-31 00:00:00', $split[1]->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-01-31 23:59:59', $split[1]->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-02-01 00:00:00', $split[2]->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-02-28 23:59:59', $split[2]->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-03-01 00:00:00', $split[3]->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-03-01 23:59:59', $split[3]->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-03-02 00:00:00', $split[4]->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-03-02 02:59:59', $split[4]->getEndDate()->format('Y-m-d H:i:s'));
    }

    public function testSplitWithReceivedOnWholeMonth(): void
    {
        $query = new QueryByFilters(
            new DateTimeImmutable('2026-02-01 00:00:00'),
            new DateTimeImmutable('2026-02-28 23:59:59'),
            DownloadType::recibidos(),
        );
        $splitter = new MetadataPackageQuerySplitter($query);

        $split = iterator_to_array($splitter->split());

        $this->assertCount(1, $split);
        $this->assertSame('2026-02-01 00:00:00', $split[0]->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-02-28 23:59:59', $split[0]->getEndDate()->format('Y-m-d H:i:s'));
    }

    public function testSplitWithReceivedOnSameDay(): void
    {
        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-15 01:02:03'),
            new DateTimeImmutable('2026-01-15 21:22:23'),
            DownloadType::recibidos(),
        );
        $splitter = new MetadataPackageQuerySplitter($query);

        $split = iterator_to_array($splitter->split());

        $this->assertCount(1, $split);
        $this->assertSame($query, $split[0]);
    }

    public function testSplitWithIssuedMultiMonth(): void
    {
        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-15 00:00:00'),
            new DateTimeImmutable('2026-03-10 23:59:59'),
            DownloadType::emitidos(),
        );
        $splitter = new MetadataPackageQuerySplitter($query);

        $split = iterator_to_array($splitter->split());

        $this->assertCount(1, $split);
        $this->assertSame($query, $split[0]);
    }
}
