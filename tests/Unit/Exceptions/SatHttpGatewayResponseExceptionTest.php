<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Exceptions;

use PhpCfdi\CfdiSatScraper\Exceptions\SatException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayResponseException;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

final class SatHttpGatewayResponseExceptionTest extends TestCase
{
    public function testUnexpectedEmptyResponse(): void
    {
        $when = 'testing';
        $response = $this->createMock(ResponseInterface::class);
        $method = 'post';
        $url = 'https://example.com/';
        $requestHeaders = ['referer' => 'https://external.com/'];
        $requestData = ['foo' => 'bar'];

        $exception = SatHttpGatewayResponseException::unexpectedEmptyResponse(
            $when,
            $response,
            $method,
            $url,
            $requestHeaders,
            $requestData,
        );

        $this->assertInstanceOf(SatException::class, $exception);
        $this->assertInstanceOf(SatHttpGatewayException::class, $exception);
        $this->assertSame("Unexpected empty content when $when", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
        $this->assertSame($response, $exception->getResponse());
        $this->assertSame($method, $exception->getHttpMethod());
        $this->assertSame($url, $exception->getUrl());
        $this->assertSame($requestHeaders, $exception->getRequestHeaders());
        $this->assertSame($requestData, $exception->getRequestData());
    }
}
