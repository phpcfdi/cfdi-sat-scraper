<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Internal;

use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use PhpCfdi\CfdiSatScraper\Contracts\ResourceDownloadHandlerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadResponseError;
use PhpCfdi\CfdiSatScraper\Internal\ResourceDownloaderPromiseHandler;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use RuntimeException;

final class ResourceDownloaderPromiseHandlerTest extends TestCase
{
    public function testValidateResponseWithInvalidStatusCode(): void
    {
        $resourceDownloadHandler = $this->createMock(ResourceDownloadHandlerInterface::class);
        $response = new Response(400);
        $uuid = 'b01017a7-e1b8-4a25-9e31-85ab56526f54';

        $handler = new ResourceDownloaderPromiseHandler(ResourceType::xml(), $resourceDownloadHandler);

        $this->expectException(ResourceDownloadResponseError::class);
        $this->expectExceptionMessage(
            sprintf('Download of CFDI %s return an invalid status code %d', $uuid, $response->getStatusCode()),
        );

        $handler->validateResponse($response, $uuid);
    }

    public function testValidateResponseWithEmptyBody(): void
    {
        $resourceDownloadHandler = $this->createMock(ResourceDownloadHandlerInterface::class);
        $response = new Response(200, body: '');
        $uuid = 'b01017a7-e1b8-4a25-9e31-85ab56526f54';

        $handler = new ResourceDownloaderPromiseHandler(ResourceType::xml(), $resourceDownloadHandler);

        $this->expectException(ResourceDownloadResponseError::class);
        $this->expectExceptionMessage(
            sprintf('Download of CFDI %s return an empty body', $uuid),
        );

        $handler->validateResponse($response, $uuid);
    }

    public function testValidateResponseIsXmlButWithoutUuid(): void
    {
        $resourceDownloadHandler = $this->createMock(ResourceDownloadHandlerInterface::class);
        $response = new Response(200, body: '<root/>');
        $uuid = 'b01017a7-e1b8-4a25-9e31-85ab56526f54';

        $handler = new ResourceDownloaderPromiseHandler(ResourceType::xml(), $resourceDownloadHandler);

        $this->expectException(ResourceDownloadResponseError::class);
        $this->expectExceptionMessage(
            sprintf('Download of CFDI %s return something that is not a CFDI', $uuid),
        );

        $handler->validateResponse($response, $uuid);
    }

    public function testValidateResponseIsPdfButMimeTypeDoesNotMatch(): void
    {
        $resourceDownloadHandler = $this->createMock(ResourceDownloadHandlerInterface::class);
        $mimeType = 'text/plain';
        $response = new Response(200, body: 'text');
        $uuid = 'b01017a7-e1b8-4a25-9e31-85ab56526f54';

        $handler = new ResourceDownloaderPromiseHandler(ResourceType::pdf(), $resourceDownloadHandler);

        $this->expectException(ResourceDownloadResponseError::class);
        $this->expectExceptionMessage(
            sprintf('Download of CFDI %s return something that is not a PDF (mime %s)', $uuid, $mimeType),
        );

        $handler->validateResponse($response, $uuid);
    }

    public function testPromiseFullfilledWithValidResponse(): void
    {
        $resourceDownloadHandler = new ResourceDownloadHandlerSpy();
        $handler = new ResourceDownloaderPromiseHandler(ResourceType::xml(), $resourceDownloadHandler);
        $uuid = 'b01017a7-e1b8-4a25-9e31-85ab56526f54';
        $cfdiContent = <<< XML
            <?xml version="1.0" encoding="UTF-8"?>
            <cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4">
                <tfd:TimbreFiscalDigital xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital" UUID="$uuid"/>
            </cfdi:Comprobante>
            XML;
        $response = new Response(200, body: $cfdiContent);
        $handler->promiseFulfilled($response, $uuid);

        $this->assertSame($uuid, $resourceDownloadHandler->lastUuid);
        $this->assertSame($cfdiContent, $resourceDownloadHandler->lastContent);
        $this->assertSame($response, $resourceDownloadHandler->lastResponse);
        $this->assertSame([$uuid], $handler->downloadedUuids());
    }

    public function testPromiseFullfilledWithErrorResponse(): void
    {
        $resourceDownloadHandler = new ResourceDownloadHandlerSpy();
        $handler = new ResourceDownloaderPromiseHandler(ResourceType::xml(), $resourceDownloadHandler);
        $uuid = 'b01017a7-e1b8-4a25-9e31-85ab56526f54';
        $response = new Response(500);
        $handler->promiseFulfilled($response, $uuid);

        $this->assertSame($uuid, $resourceDownloadHandler->lastError->getUuid());
    }

    public function testPromiseFullfilledWithException(): void
    {
        $resourceDownloadHandler = new ResourceDownloadHandlerSpy();
        $resourceDownloadHandler->onSuccessException = new Exception('Dummy exception');
        $handler = new ResourceDownloaderPromiseHandler(ResourceType::xml(), $resourceDownloadHandler);
        $uuid = 'b01017a7-e1b8-4a25-9e31-85ab56526f54';
        $cfdiContent = <<< XML
            <?xml version="1.0" encoding="UTF-8"?>
            <cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4">
                <tfd:TimbreFiscalDigital xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital" UUID="$uuid"/>
            </cfdi:Comprobante>
            XML;
        $response = new Response(200, body: $cfdiContent);
        $handler->promiseFulfilled($response, $uuid);

        $this->assertSame($uuid, $resourceDownloadHandler->lastError->getUuid());
    }

    public function testPromiseRejected(): void
    {
        $resourceDownloadHandler = new ResourceDownloadHandlerSpy();
        $handler = new ResourceDownloaderPromiseHandler(ResourceType::xml(), $resourceDownloadHandler);
        $reason = $this->createMock(RuntimeException::class);
        $uuid = 'b01017a7-e1b8-4a25-9e31-85ab56526f54';
        $handler->promiseRejected($reason, $uuid);

        $handledError = $resourceDownloadHandler->lastError;
        $this->assertSame($uuid, $handledError->getUuid());
        $this->assertSame($reason, $handledError->getReason());
    }

    public function testPromiseRejectedReasonIsRequestException(): void
    {
        $resourceDownloadHandler = new ResourceDownloadHandlerSpy();
        $handler = new ResourceDownloaderPromiseHandler(ResourceType::xml(), $resourceDownloadHandler);
        $reason = $this->createMock(RequestException::class);
        $uuid = 'b01017a7-e1b8-4a25-9e31-85ab56526f54';
        $handler->promiseRejected($reason, $uuid);

        $handledError = $resourceDownloadHandler->lastError;
        $this->assertSame($uuid, $handledError->getUuid());
        $this->assertSame($reason, $handledError->getReason());
    }
}
