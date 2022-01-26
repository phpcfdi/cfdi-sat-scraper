<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Exceptions;

use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatException;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class LogicExceptionTest extends TestCase
{
    public function testHierarchy(): void
    {
        $exception = $this->createMock(LogicException::class);
        $this->assertInstanceOf(\LogicException::class, $exception);
        $this->assertInstanceOf(SatException::class, $exception);
    }

    public function testGeneric(): void
    {
        $previous = $this->createMock(\LogicException::class);
        $exception = LogicException::generic('testing exception', $previous);
        $this->assertSame('testing exception', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
