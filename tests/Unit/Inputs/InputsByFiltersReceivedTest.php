<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Inputs;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Inputs\InputsByFiltersReceived;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class InputsByFiltersReceivedTest extends TestCase
{
    public function testWholeMonth(): void
    {
        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-01 00:00:00'),
            new DateTimeImmutable('2026-01-31 23:59:59'),
            DownloadType::recibidos(),
        );

        $filters = (new InputsByFiltersReceived($query))->getDateFilters();

        $this->assertSame('2026', $filters['ctl00$MainContent$CldFecha$DdlAnio']);
        $this->assertSame('1', $filters['ctl00$MainContent$CldFecha$DdlMes']);
        $this->assertSame('0', $filters['ctl00$MainContent$CldFecha$DdlDia']);
        $this->assertSame('0', $filters['ctl00$MainContent$CldFecha$DdlHora']);
        $this->assertSame('0', $filters['ctl00$MainContent$CldFecha$DdlMinuto']);
        $this->assertSame('0', $filters['ctl00$MainContent$CldFecha$DdlSegundo']);
        $this->assertSame('23', $filters['ctl00$MainContent$CldFecha$DdlHoraFin']);
        $this->assertSame('59', $filters['ctl00$MainContent$CldFecha$DdlMinutoFin']);
        $this->assertSame('59', $filters['ctl00$MainContent$CldFecha$DdlSegundoFin']);
    }

    public function testSameDay(): void
    {
        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-13 01:02:03'),
            new DateTimeImmutable('2026-01-13 21:22:23'),
            DownloadType::recibidos(),
        );

        $filters = (new InputsByFiltersReceived($query))->getDateFilters();

        $this->assertSame('2026', $filters['ctl00$MainContent$CldFecha$DdlAnio']);
        $this->assertSame('1', $filters['ctl00$MainContent$CldFecha$DdlMes']);
        $this->assertSame('13', $filters['ctl00$MainContent$CldFecha$DdlDia']);
        $this->assertSame('1', $filters['ctl00$MainContent$CldFecha$DdlHora']);
        $this->assertSame('2', $filters['ctl00$MainContent$CldFecha$DdlMinuto']);
        $this->assertSame('3', $filters['ctl00$MainContent$CldFecha$DdlSegundo']);
        $this->assertSame('21', $filters['ctl00$MainContent$CldFecha$DdlHoraFin']);
        $this->assertSame('22', $filters['ctl00$MainContent$CldFecha$DdlMinutoFin']);
        $this->assertSame('23', $filters['ctl00$MainContent$CldFecha$DdlSegundoFin']);
    }

    public function testDifferentDays(): void
    {
        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-13 01:02:03'),
            new DateTimeImmutable('2026-01-14 21:22:23'),
            DownloadType::recibidos(),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Query is not for only one date or for the whole month');
        new InputsByFiltersReceived($query);
    }

    public function testQueryDownloadTypeIsNotReceived(): void
    {
        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-13 01:02:03'),
            new DateTimeImmutable('2026-01-13 01:02:03'),
            DownloadType::emitidos(),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Query is not for download type received');
        new InputsByFiltersReceived($query);
    }
}
