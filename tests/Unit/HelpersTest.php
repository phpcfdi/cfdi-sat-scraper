<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Helpers;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

class HelpersTest extends TestCase
{
    public function testConverterSecondsToHoursPositive(): void
    {
        $this->assertSame(Helpers::converterSecondsToHours(86399), '23:59:59');
    }

    public function testConverterSecondsToHoursNegativeValue(): void
    {
        $this->assertSame(Helpers::converterSecondsToHours(-1000), '23:43:20');
    }

    public function testFormatNumberGreaterThan(): void
    {
        $this->assertSame(Helpers::formatNumber(12), '12');
    }

    public function testFormatNumberLessThan(): void
    {
        $this->assertSame(Helpers::formatNumber(9), '09');
    }
}
