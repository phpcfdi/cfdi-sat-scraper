<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Exceptions;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatException;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class InvalidArgumentExceptionTest extends TestCase
{
    public function testHierarchy(): void
    {
        $exception = $this->createMock(InvalidArgumentException::class);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(SatException::class, $exception);
    }

    public function testEmptyInput(): void
    {
        $exception = InvalidArgumentException::emptyInput('x-input');
        $this->assertSame(
            'Invalid argument x-input is empty',
            $exception->getMessage(),
        );
    }

    public function testPeriodStartDateGreaterThanEndDate(): void
    {
        $since = new DateTimeImmutable('2021-12-31');
        $until = new DateTimeImmutable('2021-01-01');
        $exception = InvalidArgumentException::periodStartDateGreaterThanEndDate($since, $until);
        $this->assertSame(
            'The start date 2021-12-31 00:00:00 is greater than the end date 2021-01-01 00:00:00',
            $exception->getMessage(),
        );
    }

    public function testComplementsOptionInvalidKey(): void
    {
        $exception = InvalidArgumentException::complementsOptionInvalidKey('x-key');
        $this->assertSame(
            "The key 'x-key' is not registered as a valid option for ComplementsOption",
            $exception->getMessage(),
        );
    }
}
