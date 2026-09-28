<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayResponseException;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PhpCfdi\CfdiSatScraper\URLS;
use Psr\Http\Message\RequestInterface;

final class SatHttpGatewayTest extends TestCase
{
    public function testNewObjectHasCookieJarEmpty(): void
    {
        $gateway = new SatHttpGateway();
        $this->assertTrue($gateway->isCookieJarEmpty());
    }

    public function testPostJsonSendsRawBodyAndContentType(): void
    {
        $history = [];
        $mock = new MockHandler([new Response(200, [], '{"d":"ok"}')]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));
        $gateway = new SatHttpGateway(new Client(['handler' => $stack]));

        $contents = $gateway->postJson('https://example.com/endpoint', "{'Parametros':'x'}");

        $this->assertSame('{"d":"ok"}', $contents);
        /** @phpstan-var list<array{request: RequestInterface}> $history */
        $this->assertCount(1, $history);
        /** @var RequestInterface $request */
        $request = $history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('application/json; charset=utf-8', $request->getHeaderLine('Content-Type'));
        $this->assertSame("{'Parametros':'x'}", strval($request->getBody()));
    }

    public function testPostDownloadPackageHHeadersAndData(): void
    {
        $history = [];
        $mock = new MockHandler([new Response(200, [], 'ZIP-CONTENTS')]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));
        $gateway = new SatHttpGateway(new Client(['handler' => $stack]));
        $url = URLS::PORTAL_CFDI_DESCARGA_MASIVA;
        $headerHost = (string) parse_url($url, PHP_URL_HOST);

        $contents = $gateway->postDownloadPackage(['foo' => 'bar']);

        $this->assertSame('ZIP-CONTENTS', $contents);
        /** @phpstan-var list<array{request: RequestInterface}> $history */
        $this->assertCount(1, $history);
        /** @var RequestInterface $request */
        $request = $history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame($url, (string) $request->getUri());
        $this->assertStringContainsString('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
        $this->assertSame('foo=bar', strval($request->getBody()));
        $this->assertSame($headerHost, $request->getHeaderLine('Host'));
        $this->assertSame($url, $request->getHeaderLine('Referer'));
        $this->assertSame('identity', $request->getHeaderLine('Accept-Encoding'));
    }

    public function testPostJsonWithEmptyResponseThrowsException(): void
    {
        $mock = new MockHandler([new Response(200, [], '')]);
        $gateway = new SatHttpGateway(new Client(['handler' => HandlerStack::create($mock)]));

        $this->expectException(SatHttpGatewayResponseException::class);
        $gateway->postJson('https://example.com/endpoint', '{}');
    }
}
