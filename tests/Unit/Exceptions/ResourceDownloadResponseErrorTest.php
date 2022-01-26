<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Exceptions;

use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadError;
use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadResponseError;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class ResourceDownloadResponseErrorTest extends TestCase
{
    public function testHierarchy(): void
    {
        $exception = $this->createMock(ResourceDownloadResponseError::class);
        $this->assertInstanceOf(ResourceDownloadError::class, $exception);
    }

    public function testInvalidStatusCode(): void
    {
        /** @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn('503');
        $uuid = 'uuid';
        $exception = ResourceDownloadResponseError::invalidStatusCode($response, $uuid);
        $this->assertSame(
            'Download of CFDI uuid return an invalid status code 503',
            $exception->getMessage(),
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
        $this->assertSame($uuid, $exception->getUuid());
        $this->assertSame($response, $exception->getReason());
    }

    public function testEmptyContent(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $uuid = 'uuid';
        $exception = ResourceDownloadResponseError::emptyContent($response, $uuid);
        $this->assertSame('Download of CFDI uuid return an empty body', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
        $this->assertSame($uuid, $exception->getUuid());
        $this->assertSame($response, $exception->getReason());
    }

    public function testContentIsNotCfdi(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $uuid = 'uuid';
        $exception = ResourceDownloadResponseError::contentIsNotCfdi($response, $uuid);
        $this->assertSame('Download of CFDI uuid return something that is not a CFDI', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
        $this->assertSame($uuid, $exception->getUuid());
        $this->assertSame($response, $exception->getReason());
    }

    public function testContentIsNotPdf(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $uuid = 'uuid';
        $exception = ResourceDownloadResponseError::contentIsNotPdf($response, $uuid, 'text/plain');
        $this->assertSame(
            'Download of CFDI uuid return something that is not a PDF (mime text/plain)',
            $exception->getMessage(),
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
        $this->assertSame($uuid, $exception->getUuid());
        $this->assertSame($response, $exception->getReason());
    }

    public function testOnSuccessException(): void
    {
        $previous = $this->createMock(Throwable::class);
        $response = $this->createMock(ResponseInterface::class);
        $uuid = 'uuid';
        $exception = ResourceDownloadResponseError::onSuccessException($response, $uuid, $previous);
        $this->assertSame('Download of CFDI uuid was unable to handle fulfill', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame($uuid, $exception->getUuid());
        $this->assertSame($response, $exception->getReason());
    }

    public function testOnSuccessExceptionWhenExceptionIsAResourceDownloadResponseError(): void
    {
        $previous = $this->createMock(ResourceDownloadResponseError::class);
        $response = $this->createMock(ResponseInterface::class);
        $uuid = 'uuid';
        $exception = ResourceDownloadResponseError::onSuccessException($response, $uuid, $previous);
        $this->assertSame($previous, $exception);
    }
}
