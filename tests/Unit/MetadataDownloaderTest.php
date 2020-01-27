<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\MetadataDownloader;
use PhpCfdi\CfdiSatScraper\Query;
use PhpCfdi\CfdiSatScraper\QueryResolver;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class MetadataDownloaderTest extends TestCase
{
    public function testConstructor(): void
    {
        $resolver = $this->createMock(QueryResolver::class);
        $callable = null;
        $downloader = new MetadataDownloader($resolver, $callable);
        $this->assertSame($resolver, $downloader->getQueryResolver());
        $this->assertSame($callable, $downloader->getOnFiveHundred());
    }

    public function testRaiseOnLimitWithConfiguredCallable(): void
    {
        $resolver = $this->createMock(QueryResolver::class);
        $dateOnCallable = null;
        $downloader = new MetadataDownloader(
            $resolver,
            function (\DateTimeImmutable $date) use (&$dateOnCallable): void {
                $dateOnCallable = $date;
            }
        );

        $expectedDate = new \DateTimeImmutable();
        $downloader->raiseOnLimit($expectedDate);
        $this->assertSame($expectedDate, $dateOnCallable);
    }

    public function testRaiseOnLimitWithOutCallable(): void
    {
        $resolver = $this->createMock(QueryResolver::class);
        $downloader = new MetadataDownloader($resolver, null);

        $date = new \DateTimeImmutable();
        $downloader->raiseOnLimit($date);
        $this->assertTrue(true, 'Assert that no exception was thrown');
    }

    public function testBuildDateWithDayAndSeconds(): void
    {
        $resolver = $this->createMock(QueryResolver::class);
        $downloader = new MetadataDownloader($resolver, null);

        $date = new \DateTimeImmutable('2019-01-13 14:15:16');
        $seconds = 84265; // 23:24:25
        $transformed = $downloader->buildDateWithDayAndSeconds($date, $seconds);
        $this->assertSame('2019-01-13 23:24:25', $transformed->format('Y-m-d H:i:s'));
    }

    public function testResolveQueryUsesQueryResolver(): void
    {
        $start = new \DateTimeImmutable('2012-11-16 00:00:00');
        $end = new \DateTimeImmutable('2012-11-16 23:59:59');
        $query = new Query($start, $end);
        $resolver = new FakeQueryResolver();
        $downloader = new MetadataDownloader($resolver, null);
        $downloader->resolveQuery($query);
        $expected = [
            ['start' => '2012-11-16 00:00:00', 'end' => '2012-11-16 23:59:59', 'count' => 0],
        ];
        $this->assertSame($expected, $resolver->resolveCalls);
    }

    public function testNewQueryWithSeconds(): void
    {
        $baseStart = new \DateTimeImmutable('2019-01-13 14:15:16');
        $baseEnd = new \DateTimeImmutable('2019-01-14 15:16:17');
        $baseQuery = new Query($baseStart, $baseEnd);

        $resolver = $this->createMock(QueryResolver::class);
        $downloader = new MetadataDownloader($resolver, null);

        $query = $downloader->newQueryWithSeconds($baseQuery, 1, 2);
        $this->assertSame('2019-01-13 00:00:01', $query->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2019-01-14 00:00:02', $query->getEndDate()->format('Y-m-d H:i:s'));
        // also check that passed query was not changed
        $this->assertSame($baseStart, $baseQuery->getStartDate());
        $this->assertSame($baseEnd, $baseQuery->getEndDate());
    }

    public function testDownloadQueryCreatesCorrectIntervals(): void
    {
        // observe that dates does not start at 00:00:00 or end at 23:59:59
        $baseStart = new \DateTimeImmutable('2019-01-13 00:00:01');
        $baseEnd = new \DateTimeImmutable('2019-01-13 00:00:58');
        $baseQuery = new Query($baseStart, $baseEnd);

        // The downloader will emulate that there are 250 records at 00:00:10, 00:00:25, 00:00:40, 00:00:55
        $fakes = $this->fakes();
        $resolver = new FakeQueryResolver();
        $resolver->appendMoment(new \DateTimeImmutable('2019-01-13 00:00:10'), $fakes->doMetadataList(250));
        $resolver->appendMoment(new \DateTimeImmutable('2019-01-13 00:00:25'), $fakes->doMetadataList(250));
        $resolver->appendMoment(new \DateTimeImmutable('2019-01-13 00:00:40'), $fakes->doMetadataList(250));
        $resolver->appendMoment(new \DateTimeImmutable('2019-01-13 00:00:55'), $fakes->doMetadataList(250));

        $reachedLimits = [];
        $callable = function (\DateTimeImmutable $date) use (&$reachedLimits): void {
            $reachedLimits[] = $date->format('Y-m-d H:i:s');
        };
        $downloader = new MetadataDownloader($resolver, $callable);

        // prepare expected output
        $expectedInfo = [
            ['start' => '2019-01-13 00:00:01', 'end' => '2019-01-13 00:00:58', 'count' => 1000],
            ['start' => '2019-01-13 00:00:01', 'end' => '2019-01-13 00:00:29', 'count' => 500],
            ['start' => '2019-01-13 00:00:01', 'end' => '2019-01-13 00:00:15', 'count' => 250],
            ['start' => '2019-01-13 00:00:16', 'end' => '2019-01-13 00:00:58', 'count' => 750],
            ['start' => '2019-01-13 00:00:16', 'end' => '2019-01-13 00:00:37', 'count' => 250],
            ['start' => '2019-01-13 00:00:38', 'end' => '2019-01-13 00:00:58', 'count' => 500],
            ['start' => '2019-01-13 00:00:38', 'end' => '2019-01-13 00:00:48', 'count' => 250],
            ['start' => '2019-01-13 00:00:49', 'end' => '2019-01-13 00:00:58', 'count' => 250],
        ];

        // fire and check
        $downloader->downloadQuery($baseQuery);
        $this->assertSame($expectedInfo, $resolver->resolveCalls);
        $this->assertCount(0, $reachedLimits, 'downloadQuery should never raise with given example');
    }

    public function testDownloadQueryWithLimitReached(): void
    {
        $baseStart = new \DateTimeImmutable('2019-01-13 00:00:00');
        $baseEnd = new \DateTimeImmutable('2019-01-13 00:00:04');
        $baseQuery = new Query($baseStart, $baseEnd);

        // The downloader will emulate that there are 500 records at 00:00:00 & 00:00:04
        $fakes = $this->fakes();
        $resolver = new FakeQueryResolver();
        $resolver->appendMoment(new \DateTimeImmutable('2019-01-13 00:00:00'), $fakes->doMetadataList(500));
        $resolver->appendMoment(new \DateTimeImmutable('2019-01-13 00:00:04'), $fakes->doMetadataList(500));

        $reachedLimits = [];
        $callable = function (\DateTimeImmutable $date) use (&$reachedLimits): void {
            $reachedLimits[] = $date->format('Y-m-d H:i:s');
        };
        $downloader = new MetadataDownloader($resolver, $callable);

        // prepare expected output
        $expectedInfo = [
            ['start' => '2019-01-13 00:00:00', 'end' => '2019-01-13 00:00:04', 'count' => 1000],
            ['start' => '2019-01-13 00:00:00', 'end' => '2019-01-13 00:00:02', 'count' => 500],
            ['start' => '2019-01-13 00:00:00', 'end' => '2019-01-13 00:00:01', 'count' => 500],
            ['start' => '2019-01-13 00:00:00', 'end' => '2019-01-13 00:00:00', 'count' => 500],
            ['start' => '2019-01-13 00:00:01', 'end' => '2019-01-13 00:00:04', 'count' => 500],
            ['start' => '2019-01-13 00:00:01', 'end' => '2019-01-13 00:00:02', 'count' => 0],
            ['start' => '2019-01-13 00:00:03', 'end' => '2019-01-13 00:00:04', 'count' => 500],
            ['start' => '2019-01-13 00:00:03', 'end' => '2019-01-13 00:00:03', 'count' => 0],
            ['start' => '2019-01-13 00:00:04', 'end' => '2019-01-13 00:00:04', 'count' => 500],
        ];

        // fire and check
        $downloader->downloadQuery($baseQuery);
        $this->assertSame($expectedInfo, $resolver->resolveCalls);
        $this->assertSame([
            '2019-01-13 00:00:00',
            '2019-01-13 00:00:04',
        ], $reachedLimits);
    }

    public function testDownloadByDateTime(): void
    {
        $resolver = new FakeQueryResolver();
        $downloader = new MetadataDownloader($resolver, null);

        $start = new \DateTimeImmutable('2019-01-13 14:15:16');
        $end = new \DateTimeImmutable('2019-01-15 16:17:18');
        $query = new Query($start, $end);

        $downloader->downloadByDateTime($query);
        $this->assertSame([
            ['start' => '2019-01-13 14:15:16', 'end' => '2019-01-13 23:59:59', 'count' => 0],
            ['start' => '2019-01-14 00:00:00', 'end' => '2019-01-14 23:59:59', 'count' => 0],
            ['start' => '2019-01-15 00:00:00', 'end' => '2019-01-15 16:17:18', 'count' => 0],
        ], $resolver->resolveCalls);
    }

    public function testDownloadByDate(): void
    {
        $resolver = new FakeQueryResolver();
        $downloader = new MetadataDownloader($resolver, null);

        $start = new \DateTimeImmutable('2019-01-13 14:15:16');
        $end = new \DateTimeImmutable('2019-01-15 16:17:18');
        $query = new Query($start, $end);

        $downloader->downloadByDate($query);
        $this->assertSame([
            ['start' => '2019-01-13 00:00:00', 'end' => '2019-01-13 23:59:59', 'count' => 0],
            ['start' => '2019-01-14 00:00:00', 'end' => '2019-01-14 23:59:59', 'count' => 0],
            ['start' => '2019-01-15 00:00:00', 'end' => '2019-01-15 23:59:59', 'count' => 0],
        ], $resolver->resolveCalls);
    }

    public function testDownloadByUuids(): void
    {
        $fakes = $this->fakes();
        $resolver = new FakeQueryResolver();
        $downloader = new MetadataDownloader($resolver, null);

        $downloader->downloadByUuids([
            $fakes->faker()->uuid,
            $fakes->faker()->uuid,
            $fakes->faker()->uuid,
        ], DownloadTypesOption::recibidos());

        $this->assertCount(3, $resolver->resolveCalls);
    }
}
