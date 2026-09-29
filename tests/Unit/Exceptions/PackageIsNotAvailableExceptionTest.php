<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Exceptions;

use Exception;
use PhpCfdi\CfdiSatScraper\Exceptions\MetadataPackageException;
use PhpCfdi\CfdiSatScraper\Exceptions\PackageIsNotAvailableException;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class PackageIsNotAvailableExceptionTest extends TestCase
{
    public function testExceptionProperties(): void
    {
        $uuid = 'FOO-BAR';
        $expectedMessage = sprintf('The package with UUID %s is not available for download', $uuid);

        $previous = new Exception('foo');
        $exception = new PackageIsNotAvailableException($uuid, $previous);

        $this->assertInstanceOf(MetadataPackageException::class, $exception);
        $this->assertSame($exception->getMessage(), $expectedMessage);
        $this->assertSame($exception->getPrevious(), $previous);
    }
}
