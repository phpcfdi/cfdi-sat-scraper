<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Exceptions;

use GuzzleHttp\Exception\GuzzleException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayClientException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class SatHttpGatewayClientExceptionTest extends TestCase
{
    public function testClientException(): void
    {
        $when = 'testing';
        $method = 'post';
        $url = 'https://example.com/';
        $requestHeaders = ['referer' => 'https://external.com/'];
        $requestData = ['foo' => 'bar'];
        $guzzleException = $this->createMock(GuzzleException::class);

        $exception = SatHttpGatewayClientException::clientException(
            $when,
            $method,
            $url,
            $requestHeaders,
            $requestData,
            $guzzleException,
        );

        $this->assertInstanceOf(SatException::class, $exception);
        $this->assertInstanceOf(SatHttpGatewayException::class, $exception);
        $this->assertSame("HTTP client error when $when", $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($guzzleException, $exception->getPrevious());
        $this->assertSame($method, $exception->getHttpMethod());
        $this->assertSame($url, $exception->getUrl());
        $this->assertSame($requestHeaders, $exception->getRequestHeaders());
        $this->assertSame($requestData, $exception->getRequestData());
        $this->assertSame($guzzleException, $exception->getClientException());
    }
}
