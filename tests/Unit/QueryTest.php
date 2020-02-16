<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Filters\Options\ComplementsOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\RfcOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\Query;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class QueryTest extends TestCase
{
    public function testFinalDateLessThanInitialDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Query(new \DateTimeImmutable('2019-01-31'), new \DateTimeImmutable('2019-01-01'));
    }

    public function testDefaultOptions(): void
    {
        $query = new Query(new \DateTimeImmutable('2019-01-01'), new \DateTimeImmutable('2019-01-31'));
        $this->assertTrue($query->getDownloadType()->isRecibidos());
        $this->assertTrue($query->getComplement()->isTodos());
        $this->assertTrue($query->getStateVoucher()->isTodos());
    }

    public function testSetDatesFromConstructor(): void
    {
        $start = new \DateTimeImmutable('2019-01-01');
        $end = new \DateTimeImmutable('2019-01-31');
        $query = new Query($start, $end);

        $this->assertEquals($query->getStartDate(), $start);
        $this->assertEquals($query->getEndDate(), $end);
    }

    public function testSetDatesFromSetters(): void
    {
        $start = new \DateTimeImmutable('2019-01-15');
        $end = new \DateTimeImmutable('2019-01-17');

        $query = new Query(new \DateTimeImmutable('2019-01-01'), new \DateTimeImmutable('2019-01-31'));
        $query->setStartDate($start);
        $query->setEndDate($end);

        $this->assertEquals($query->getStartDate(), $start);
        $this->assertEquals($query->getEndDate(), $end);
    }

    public function testSetComplementOption(): void
    {
        $query = new Query(new \DateTimeImmutable('2019-01-01'), new \DateTimeImmutable('2019-01-31'));
        $query->setComplement(ComplementsOption::aerolineas());

        $this->assertTrue($query->getComplement()->isAerolineas());
    }

    public function testSetDownloadTypeOption(): void
    {
        $query = new Query(new \DateTimeImmutable('2019-01-01'), new \DateTimeImmutable('2019-01-31'));
        $query->setDownloadType(DownloadTypesOption::recibidos());

        $this->assertTrue($query->getDownloadType()->isRecibidos());
    }

    public function testSetRfcOption(): void
    {
        $rfc = 'ABGC930521D34';
        $query = new Query(new \DateTimeImmutable('2019-01-01'), new \DateTimeImmutable('2019-01-31'));
        $query->setRfc(new RfcOption($rfc));

        $this->assertEquals($query->getRfc()->value(), $rfc);
    }

    public function testSplitByDays(): void
    {
        $lowerBound = new \DateTimeImmutable('2019-01-13 14:15:16');
        $upperBound = new \DateTimeImmutable('2019-01-15 18:19:20');
        $query = new Query($lowerBound, $upperBound);
        $splitted = [];
        foreach ($query->splitByDays() as $current) {
            $splitted[] = [
                'begin' => $current->getStartDate(),
                'end' => $current->getEndDate(),
            ];
        }

        $expected = [
            ['begin' => $lowerBound, 'end' => new \DateTimeImmutable('2019-01-13 23:59:59')],
            ['begin' => new \DateTimeImmutable('2019-01-14 00:00:00'), 'end' => new \DateTimeImmutable('2019-01-14 23:59:59')],
            ['begin' => new \DateTimeImmutable('2019-01-15 00:00:00'), 'end' => $upperBound],
        ];

        $this->assertEquals($expected, $splitted);
    }

    public function testSplitByDaysPreserveOtherFilters(): void
    {
        // this options are not the default
        $downloadType = DownloadTypesOption::emitidos();
        $complement = ComplementsOption::comercioExterior11();
        $stateVoucher = StatesVoucherOption::cancelados();

        $lowerBound = new \DateTimeImmutable('2019-01-13 14:15:16');
        $upperBound = new \DateTimeImmutable('2019-01-15 18:19:20');
        $query = new Query($lowerBound, $upperBound);
        $query->setDownloadType($downloadType);
        $query->setComplement($complement);
        $query->setStateVoucher($stateVoucher);
        foreach ($query->splitByDays() as $current) {
            $this->assertEquals($downloadType, $current->getDownloadType());
            $this->assertEquals($complement, $current->getComplement());
            $this->assertEquals($stateVoucher, $current->getStateVoucher());
        }
    }
}
