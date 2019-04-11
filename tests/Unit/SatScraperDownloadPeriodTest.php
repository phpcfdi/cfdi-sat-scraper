<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Query;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SatScraperDownloadPeriodTest extends TestCase
{
    public function test_download_period_calls_download_day_for_every_day_between_start_and_end(): void
    {
        $start = new \DateTimeImmutable('2019-01-13 14:15:16');
        $end = new \DateTimeImmutable('2019-01-15 00:00:01');
        $query = new Query($start, $end);

        /** @var SATScraper&MockObject $scrapper */
        $scrapper = $this
            ->getMockBuilder(SATScraper::class)
            ->disableOriginalConstructor()
            ->setMethods(['initScraper', 'downloadDay'])
            ->getMock();
        $scrapper->expects($spy = $this->any())->method('downloadDay');

        $scrapper->downloadPeriod($query);
        $dates = [];
        foreach ($spy->getInvocations() as $invocation) {
            /** @var \DateTimeImmutable $date */
            $date = $invocation->getParameters()[0];
            $dates[] = $date->format('Y-m-d H:i:s');
        }

        $expectedDates = [
            '2019-01-13 00:00:00',
            '2019-01-14 00:00:00',
            '2019-01-15 00:00:00',
        ];

        $this->assertSame($expectedDates, $dates);
    }
}
