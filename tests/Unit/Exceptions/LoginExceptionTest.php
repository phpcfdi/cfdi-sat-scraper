<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Exceptions;

use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatException;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use Throwable;

final class LoginExceptionTest extends TestCase
{
    public function testHierarchy(): void
    {
        $exception = $this->createMock(LoginException::class);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertInstanceOf(SatException::class, $exception);
    }

    public function testProperties(): void
    {
        $message = 'x-message';
        $contents = 'x-contents';
        $previous = $this->createMock(Throwable::class);

        $exception = new class ($message, $contents, $previous) extends LoginException {
        };

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame($contents, $exception->getContents());
    }
}
