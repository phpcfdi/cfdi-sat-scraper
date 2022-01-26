<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Exceptions;

use PhpCfdi\CfdiSatScraper\Exceptions\RuntimeException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatException;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use Throwable;

final class RuntimeExceptionTest extends TestCase
{
    public function testHierarchy(): void
    {
        $exception = $this->createMock(RuntimeException::class);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertInstanceOf(SatException::class, $exception);
    }

    public function testPathDoesNotExists(): void
    {
        $exception = RuntimeException::pathDoesNotExists('/path/to/file');
        $this->assertSame('The path /path/to/file does not exists', $exception->getMessage());
    }

    public function testPathIsNotFolder(): void
    {
        $exception = RuntimeException::pathIsNotFolder('/path/to/file');
        $this->assertSame('The path /path/to/file is not a folder', $exception->getMessage());
    }

    public function testUnableToCreateFolder(): void
    {
        $previous = $this->createMock(Throwable::class);
        $exception = RuntimeException::unableToCreateFolder('/path/to/file', $previous);
        $this->assertSame('Unable to create folder /path/to/file', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testUnableToFilePutContents(): void
    {
        $previous = $this->createMock(Throwable::class);
        $exception = RuntimeException::unableToFilePutContents('/path/to/file', $previous);
        $this->assertSame('Unable to put contents on /path/to/file', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testUnableToFindCaptchaImage(): void
    {
        $exception = RuntimeException::unableToFindCaptchaImage('#captcha');
        $this->assertSame("Unable to find image using filter '#captcha'", $exception->getMessage());
    }
}
