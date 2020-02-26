<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use DateTimeImmutable;
use InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Filters\Options\ComplementsOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\RfcOption;
use PhpCfdi\CfdiSatScraper\QueryByFilters as Query;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class QueryByFiltersTest extends TestCase
{
    public function testFinalDateLessThanInitialDate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Query(new DateTimeImmutable('2019-01-31'), new DateTimeImmutable('2019-01-01'));
    }

    public function testDefaultOptions(): void
    {
        $query = new Query(new DateTimeImmutable('2019-01-01'), new DateTimeImmutable('2019-01-31'));
        $this->assertTrue($query->getDownloadType()->isEmitidos());
        $this->assertTrue($query->getComplement()->isTodos());
        $this->assertTrue($query->getStateVoucher()->isTodos());
    }

    public function testSetDatesFromConstructor(): void
    {
        $start = new DateTimeImmutable('2019-01-01');
        $end = new DateTimeImmutable('2019-01-31');
        $query = new Query($start, $end);

        $this->assertEquals($query->getStartDate(), $start);
        $this->assertEquals($query->getEndDate(), $end);
    }

    public function testSetDatesFromSettersPeriod(): void
    {
        $start = new DateTimeImmutable('2019-01-15');
        $end = new DateTimeImmutable('2019-01-17');

        $query = new Query(new DateTimeImmutable('2019-01-01'), new DateTimeImmutable('2019-01-31'));
        $query->setPeriod($start, $end);

        $this->assertEquals($query->getStartDate(), $start);
        $this->assertEquals($query->getEndDate(), $end);
    }

    public function testSetDatesFromSetStartEndDate(): void
    {
        $start = new DateTimeImmutable('2019-01-15');
        $end = new DateTimeImmutable('2019-01-17');

        $query = new Query(new DateTimeImmutable('2019-01-01'), new DateTimeImmutable('2019-01-31'));
        $query->setStartDate($start);
        $query->setEndDate($end);

        $this->assertEquals($query->getStartDate(), $start);
        $this->assertEquals($query->getEndDate(), $end);
    }

    public function testSetComplementOption(): void
    {
        $query = new Query(new DateTimeImmutable('2019-01-01'), new DateTimeImmutable('2019-01-31'));
        $query->setComplement(ComplementsOption::aerolineas());

        $this->assertTrue($query->getComplement()->isAerolineas());
    }

    public function testSetDownloadTypeOption(): void
    {
        $query = new Query(new DateTimeImmutable('2019-01-01'), new DateTimeImmutable('2019-01-31'));
        $query->setDownloadType(DownloadType::recibidos());

        $this->assertTrue($query->getDownloadType()->isRecibidos());
    }

    public function testSetRfcOption(): void
    {
        $rfc = 'ABGC930521D34';
        $query = new Query(new DateTimeImmutable('2019-01-01'), new DateTimeImmutable('2019-01-31'));
        $query->setRfc(new RfcOption($rfc));

        $this->assertEquals($query->getRfc()->value(), $rfc);
    }
}
