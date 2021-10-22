<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Exceptions;

use GuzzleHttp\Exception\RequestException;
use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadError;
use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadRequestExceptionError;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class ResourceDownloadRequestExceptionErrorTest extends TestCase
{
    public function testHierarchy(): void
    {
        $exception = $this->createMock(ResourceDownloadRequestExceptionError::class);
        $this->assertInstanceOf(ResourceDownloadError::class, $exception);
    }

    public function testOnRequestException(): void
    {
        $requestException = $this->createMock(RequestException::class);
        $uuid = 'uuid';
        $exception = ResourceDownloadRequestExceptionError::onRequestException($requestException, $uuid);
        $this->assertSame('Download of CFDI uuid fails when performing request', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($requestException, $exception->getPrevious());
        $this->assertSame($uuid, $exception->getUuid());
        $this->assertSame($requestException, $exception->getReason());
    }
}
