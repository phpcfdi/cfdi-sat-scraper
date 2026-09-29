<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Inputs;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Inputs\InputsByFiltersIssued;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class InputsByFiltersIssuedTest extends TestCase
{
    public function testDifferentDatesByYear(): void
    {
        $query = new QueryByFilters(
            new DateTimeImmutable('2025-01-01 00:00:00'),
            new DateTimeImmutable('2026-09-30 23:59:59'),
            DownloadType::emitidos(),
        );

        $filters = (new InputsByFiltersIssued($query))->getDateFilters();

        // 2025-01-01 00:00:00
        $this->assertSame('2025', $filters['ctl00$MainContent$hfInicial']);
        $this->assertSame('01/01/2025', $filters['ctl00$MainContent$CldFechaInicial2$Calendario_text']);
        $this->assertSame('0', $filters['ctl00$MainContent$CldFechaInicial2$DdlHora']);
        $this->assertSame('0', $filters['ctl00$MainContent$CldFechaInicial2$DdlMinuto']);
        $this->assertSame('0', $filters['ctl00$MainContent$CldFechaInicial2$DdlSegundo']);

        // 2026-09-30 23:59:59
        $this->assertSame('2026', $filters['ctl00$MainContent$hfFinal']);
        $this->assertSame('30/09/2026', $filters['ctl00$MainContent$CldFechaFinal2$Calendario_text']);
        $this->assertSame('23', $filters['ctl00$MainContent$CldFechaFinal2$DdlHora']);
        $this->assertSame('59', $filters['ctl00$MainContent$CldFechaFinal2$DdlMinuto']);
        $this->assertSame('59', $filters['ctl00$MainContent$CldFechaFinal2$DdlSegundo']);
    }

    public function testQueryDownloadTypeIsNotIssued(): void
    {
        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-13 01:02:03'),
            new DateTimeImmutable('2026-01-13 01:02:03'),
            DownloadType::recibidos(),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Query is not for download type issued');
        new InputsByFiltersIssued($query);
    }
}
