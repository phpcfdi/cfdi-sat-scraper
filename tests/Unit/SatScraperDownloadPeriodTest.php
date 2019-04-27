<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Query;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SatScraperDownloadPeriodTest extends TestCase
{
    public function test_download_period_changes_times(): void
    {
        /** @var SATScraper&MockObject $scrapper */
        $scrapper = $this
            ->getMockBuilder(SATScraper::class)
            ->disableOriginalConstructor()
            ->setMethods(['initScraper', 'downloadByDateTime'])
            ->getMock();
        $scrapper->expects($spy = $this->any())->method('downloadByDateTime');

        $start = new \DateTimeImmutable('2019-01-13 14:15:16');
        $end = new \DateTimeImmutable('2019-01-15 00:00:01');
        $query = new Query($start, $end);
        $scrapper->downloadPeriod($query);

        $this->assertSame(1, $spy->getInvocationCount());
        /** @var Query $modified */
        $modified = $spy->getInvocations()[0]->getParameters()[0];
        $this->assertSame('2019-01-13 00:00:00', $modified->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2019-01-15 23:59:59', $modified->getEndDate()->format('Y-m-d H:i:s'));
    }

    public function test_download_period_calls_download_day_for_every_day_between_start_and_end(): void
    {
        $start = new \DateTimeImmutable('2019-01-13 14:15:16');
        $end = new \DateTimeImmutable('2019-01-15 00:00:01');
        $query = new Query($start, $end);

        /** @var SATScraper&MockObject $scrapper */
        $scrapper = $this
            ->getMockBuilder(SATScraper::class)
            ->disableOriginalConstructor()
            ->setMethods(['initScraper', 'downloadQuery'])
            ->getMock();
        $scrapper->expects($spy = $this->any())->method('downloadQuery');

        $scrapper->downloadPeriod($query);
        $dates = [];
        foreach ($spy->getInvocations() as $invocation) {
            /** @var Query $query */
            $query = $invocation->getParameters()[0];
            $dates[] = $query->getStartDate()->format('Y-m-d H:i:s');
        }

        $expectedDates = [
            '2019-01-13 00:00:00',
            '2019-01-14 00:00:00',
            '2019-01-15 00:00:00',
        ];

        $this->assertSame($expectedDates, $dates);
    }
}
