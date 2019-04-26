<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\Query;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SatScraperDownloadDayTest extends TestCase
{
    public function test_download_day_splits_queries_correctly(): void
    {
        $fakes = $this->fakes();
        $dates = [];

        /** @var SATScraper&MockObject $scrapper */
        $scrapper = $this
            ->getMockBuilder(SATScraper::class)
            ->disableOriginalConstructor()
            ->setMethods(['initScraper', 'resolveQuery'])
            ->getMock();
        $scrapper->method('resolveQuery')->willReturnCallback(
            function (Query $query) use ($fakes, &$dates): MetadataList {
                // simulate that every 00:30:00, 06:30:00, 12:30:00 & 18:30:00 has 250 records each
                $howMany = 0;
                $date = $query->getStartDate()->setTime(0, 30, 0);
                while ($date < $query->getEndDate()) {
                    if ($date > $query->getStartDate()) {
                        $howMany = $howMany + 1;
                    }
                    $date = $date->modify('+ 6 hour');
                }
                $list = $fakes->doMetadataList(250 * $howMany);
                $dates[] = [
                    'start' => $query->getStartDate()->format('Y-m-d H:i:s'),
                    'end' => $query->getEndDate()->format('Y-m-d H:i:s'),
                    'count' => $list->count(),
                ];
                return $list;
            }
        );

        // setup on five hundred event
        $onFiveHundredEvents = [];
        $scrapper->setOnFiveHundred(
            function (array $values) use (&$onFiveHundredEvents): void {
                $onFiveHundredEvents[] = $values;
            }
        );

        // anyhow, this will download the hole day
        $start = new \DateTimeImmutable('2019-01-15 14:15:16');
        $end = new \DateTimeImmutable('2019-01-15 14:15:16');
        $query = new Query($start, $end);
        $scrapper->downloadPeriod($query);

        $expectedDates = [
            ['start' => '2019-01-15 00:00:00', 'end' => '2019-01-15 23:59:59', 'count' => 1000],
            ['start' => '2019-01-15 00:00:00', 'end' => '2019-01-15 11:59:59', 'count' => 500],
            ['start' => '2019-01-15 00:00:00', 'end' => '2019-01-15 05:59:59', 'count' => 250],
            ['start' => '2019-01-15 06:00:00', 'end' => '2019-01-15 23:59:59', 'count' => 750],
            ['start' => '2019-01-15 06:00:00', 'end' => '2019-01-15 14:59:59', 'count' => 500],
            ['start' => '2019-01-15 06:00:00', 'end' => '2019-01-15 10:29:59', 'count' => 250],
            ['start' => '2019-01-15 10:30:00', 'end' => '2019-01-15 23:59:59', 'count' => 500],
            ['start' => '2019-01-15 10:30:00', 'end' => '2019-01-15 17:14:59', 'count' => 250],
            ['start' => '2019-01-15 17:15:00', 'end' => '2019-01-15 23:59:59', 'count' => 250],
        ];

        $this->assertSame($expectedDates, $dates);
        $this->assertCount(0, $onFiveHundredEvents, 'downloadPeriod raised onFiveHundred without relevance');
    }

    public function test_download_day_calls_on_five_hundred(): void
    {
        $fakes = $this->fakes();

        /** @var SATScraper&MockObject $scrapper */
        $scrapper = $this
            ->getMockBuilder(SATScraper::class)
            ->disableOriginalConstructor()
            ->setMethods(['initScraper', 'resolveQuery'])
            ->getMock();
        $scrapper->method('resolveQuery')->willReturnCallback(
            function (Query $query) use ($fakes): MetadataList {
                $dateA = $query->getStartDate()->setTime(1, 2, 3); // 3723
                $dateB = $query->getStartDate()->setTime(16, 17, 18); // 58638
                if (($dateA >= $query->getStartDate() && $dateA <= $query->getEndDate())
                    || ($dateB >= $query->getStartDate() && $dateB <= $query->getEndDate())) {
                    return $fakes->doMetadataList(500);
                }
                return $fakes->doMetadataList(0);
            }
        );

        // setup on five hundred event
        $onFiveHundredEvents = [];
        $scrapper->setOnFiveHundred(
            function (array $values) use (&$onFiveHundredEvents): void {
                $onFiveHundredEvents[] = $values;
            }
        );

        // anyhow, this will download the hole day
        $start = new \DateTimeImmutable('2019-01-15 14:15:16');
        $end = new \DateTimeImmutable('2019-01-15 14:15:16');
        $query = new Query($start, $end);
        $scrapper->downloadPeriod($query);

        $expectedCalls = [
            ['count' => 500, 'year' => '2019', 'month' => '01', 'day' => '15', 'secondIni' => 3723, 'secondFin' => 3723],
            ['count' => 500, 'year' => '2019', 'month' => '01', 'day' => '15', 'secondIni' => 58638, 'secondFin' => 58638],
        ];

        $this->assertSame($expectedCalls, $onFiveHundredEvents);
    }
}
