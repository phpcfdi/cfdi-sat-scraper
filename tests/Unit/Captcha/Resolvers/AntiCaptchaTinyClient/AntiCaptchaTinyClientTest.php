<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Captcha\Resolvers\AntiCaptchaTinyClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LogicException;
use PhpCfdi\CfdiSatScraper\Captcha\Resolvers\AntiCaptchaTinyClient\AntiCaptchaTinyClient;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use RuntimeException;

final class AntiCaptchaTinyClientTest extends TestCase
{
    /** @var AntiCaptchaTinyClient */
    private $client;

    /** @var mixed[] Contains the http history */
    private $history;

    /** @var MockHandler */
    private $mock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->history = [];
        $this->mock = new MockHandler();

        $handlerStack = HandlerStack::create($this->mock);
        $handlerStack->push(Middleware::history($this->history));
        $httpClient = new Client(['handler' => $handlerStack]);

        $this->client = new AntiCaptchaTinyClient($httpClient, 'CLIENT-KEY');
    }

    /**
     * @param mixed[] $responseData
     * @return Response
     */
    public function createResponse(array $responseData): Response
    {
        return new Response(200, ['Content-Type', 'application/json'], json_encode($responseData) ?: '');
    }

    public function testCreateTaskReturnId(): void
    {
        // prepare responses
        $this->mock->append(
            $this->createResponse(['taskId' => 'TASK-ID'])
        );

        // action to test
        $taskId = $this->client->createTask('IMAGEBASE64');

        // test return value
        $this->assertSame('TASK-ID', $taskId);

        // test expected request
        $expectedRequestData = [
            'clientKey' => 'CLIENT-KEY',
            'task' => [
                'type' => 'ImageToTextTask',
                'body' => 'IMAGEBASE64',
                'phrase' => false,
                'case' => false,
                'numeric' => false,
                'math' => 0,
                'minLength' => 0,
                'maxLength' => 0,
            ],
        ];
        /** @var Request $request */
        $request = $this->history[0]['request'];
        $requestedData = json_decode($request->getBody()->__toString(), true);
        $this->assertSame('https://api.anti-captcha.com/createTask', $request->getUri()->__toString());
        $this->assertSame($expectedRequestData, $requestedData);
    }

    public function testGetTaskResultProcessing(): void
    {
        $this->mock->append(
            $this->createResponse(['status' => 'PROCESSING'])
        );

        $solution = $this->client->getTaskResult('TASK-ID');

        $this->assertSame('', $solution);

        // test expected request
        $expectedRequestData = [
            'clientKey' => 'CLIENT-KEY',
            'taskId' => 'TASK-ID',
        ];
        /** @var Request $request */
        $request = $this->history[0]['request'];
        $requestedData = json_decode($request->getBody()->__toString(), true);
        $this->assertSame('https://api.anti-captcha.com/getTaskResult', $request->getUri()->__toString());
        $this->assertSame($expectedRequestData, $requestedData);
    }

    public function testGetTaskResultReady(): void
    {
        $this->mock->append(
            $this->createResponse(['status' => 'READY', 'solution' => ['text' => 'SOLUTION']])
        );

        $solution = $this->client->getTaskResult('TASK-ID');

        $this->assertSame('SOLUTION', $solution);

        // test expected request
        $expectedRequestData = [
            'clientKey' => 'CLIENT-KEY',
            'taskId' => 'TASK-ID',
        ];
        /** @var Request $request */
        $request = $this->history[0]['request'];
        $requestedData = json_decode($request->getBody()->__toString(), true);
        $this->assertSame('https://api.anti-captcha.com/getTaskResult', $request->getUri()->__toString());
        $this->assertSame($expectedRequestData, $requestedData);
    }

    public function testGetTaskResultUnknownStatus(): void
    {
        $this->mock->append(
            $this->createResponse(['status' => 'FOO'])
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Unknown status 'FOO' for task");
        $this->client->getTaskResult('TASK-ID');
    }

    public function testGetBalance(): void
    {
        $this->mock->append(
            $this->createResponse(['balance' => 12.3456])
        );

        $balance = $this->client->getBalance();

        $this->assertEqualsWithDelta(12.3456, $balance, 0.00001);

        // test expected request
        $expectedRequestData = ['clientKey' => 'CLIENT-KEY'];
        /** @var Request $request */
        $request = $this->history[0]['request'];
        $requestedData = json_decode($request->getBody()->__toString(), true);
        $this->assertSame('https://api.anti-captcha.com/getBalance', $request->getUri()->__toString());
        $this->assertSame($expectedRequestData, $requestedData);
    }

    public function testJsonPostRequest(): void
    {
        $this->mock->append(
            $this->createResponse(['foo' => 'foo-value'])
        );

        $returnObject = $this->client->jsonPostRequest('something', ['foo' => 'bar']);

        $this->assertSame(['foo' => 'foo-value'], (array) $returnObject);

        $expectedRequestData = [
            'clientKey' => 'CLIENT-KEY', // client key must exists in the request data
            'foo' => 'bar',
        ];
        /** @var Request $request */
        $request = $this->history[0]['request'];
        $requestedData = json_decode($request->getBody()->__toString(), true);
        $this->assertSame('https://api.anti-captcha.com/something', $request->getUri()->__toString());
        $this->assertSame($expectedRequestData, $requestedData);
    }

    public function testJsonPostRequestThrowsExceptionOnError(): void
    {
        $this->mock->append(
            $this->createResponse(['errorId' => 99, 'errorDescription' => 'Testing error!']),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Anti-Captcha Error (99): Testing error!');
        $this->client->jsonPostRequest('something', []);
    }

    public function testJsonPostRequestThrowsExceptionOnHttpException(): void
    {
        $guzzleException = new RequestException('Fake exception!', new Request('GET', 'test'));
        $this->mock->append($guzzleException);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'HTTP error connecting to Anti-Captcha https://api.anti-captcha.com/something: Fake exception!'
        );
        $this->client->jsonPostRequest('something', []);
    }
}
