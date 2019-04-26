<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\Query;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

class SatScraperDownloadSecondsTest extends TestCase
{
    public function test_download_seconds_calls_resultquery_using_correct_seconds(): void
    {
        // setup an scrapper exposing protected method downloadSeconds and overriding resolveQuery
        $client = $this->createMock(Client::class);
        $cookie = $this->createMock(CookieJar::class);
        $captcha = $this->createMock(CaptchaResolverInterface::class);

        $scrapper = new class('rfc', 'ciec', $client, $cookie, $captcha) extends SATScraper {
            /** @var array */
            public $resolveQueryDates;

            public function exposeDownloadSeconds(
                Query $query,
                \DateTimeImmutable $day,
                int $startSec,
                int $endSec
            ): MetadataList {
                return $this->downloadSeconds($query, $day, $startSec, $endSec);
            }

            protected function resolveQuery(Query $query): MetadataList
            {
                $this->resolveQueryDates = [
                    'start' => $query->getStartDate()->format('Y-m-d H:i:s'),
                    'end' => $query->getEndDate()->format('Y-m-d H:i:s'),
                ];
                return new MetadataList([]);
            }
        };

        // setup arguments to use on downloadSeconds
        $start = new \DateTimeImmutable('2019-01-13 08:09:10');
        $end = new \DateTimeImmutable('2019-01-16 21:22:23');
        $query = new Query($start, $end);
        $day = new \DateTimeImmutable('2019-01-15 11:12:13');
        $startSec = 3723; // 01:02:03
        $endSec = 58638; // 16:17:18

        // execute download seconds
        $scrapper->exposeDownloadSeconds($query, $day, $startSec, $endSec);

        // perform assertions
        $expectedDates = ['start' => '2019-01-15 01:02:03', 'end' => '2019-01-15 16:17:18'];
        $this->assertSame($expectedDates, $scrapper->resolveQueryDates);
        $this->assertSame($start, $query->getStartDate(), 'Query start date was changed');
        $this->assertSame($start, $query->getStartDate(), 'Query end date was changed');
    }
}
