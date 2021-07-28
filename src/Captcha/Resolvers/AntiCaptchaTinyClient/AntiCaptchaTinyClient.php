<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Captcha\Resolvers\AntiCaptchaTinyClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use LogicException;
use RuntimeException;
use stdClass;

/**
 * This is a Guzzle based Anti-Captcha Tiny client, it allows to create a task,
 * query for a task solution and get curent balance.
 * Throws RuntimeException then HTTP error or Anti-Captcha report an error.
 */
class AntiCaptchaTinyClient
{
    public const BASE_URL = 'https://api.anti-captcha.com/';

    /** @var string */
    private $clientKey;

    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client, string $clientKey)
    {
        $this->client = $client;
        $this->clientKey = $clientKey;
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * @param string $base64Image
     * @return string
     * @throws RuntimeException When anti-captcha service return an error status
     */
    public function createTask(string $base64Image): string
    {
        $postData = [
            'task' => [
                'type' => 'ImageToTextTask',
                'body' => $base64Image,
                'phrase' => false,
                'case' => false,
                'numeric' => false,
                'math' => 0,
                'minLength' => 0,
                'maxLength' => 0,
            ],
        ];

        $result = $this->jsonPostRequest('createTask', $postData);

        return (string) $result->taskId;
    }

    /**
     * @param string $taskId
     * @return string
     * @throws RuntimeException
     */
    public function getTaskResult(string $taskId): string
    {
        $result = $this->jsonPostRequest('getTaskResult', [
            'taskId' => $taskId,
        ]);

        $antiCaptchaStatus = strtolower($result->status);
        if ('processing' === $antiCaptchaStatus) {
            return '';
        }
        if ('ready' === $antiCaptchaStatus) {
            return (string) $result->solution->text;
        }
        throw new LogicException("Unknown status '$result->status' for task");
    }

    /**
     * @return float
     * @throws RuntimeException When anti-captcha service return an error status
     */
    public function getBalance(): float
    {
        $result = $this->jsonPostRequest('getBalance', []);

        return (float) $result->balance;
    }

    /**
     * @param string $methodName
     * @param array<string, mixed> $postData
     * @return stdClass
     * @throws RuntimeException When anti-captcha service return an error status
     */
    public function jsonPostRequest(string $methodName, array $postData): stdClass
    {
        $url = self::BASE_URL . $methodName;
        try {
            $response = $this->client->request('POST', $url, [
                'json' => array_merge(['clientKey' => $this->clientKey], $postData),
            ])->getBody()->__toString();
        } catch (GuzzleException $exception) {
            $message = sprintf('HTTP error connecting to Anti-Captcha %s: %s', $url, $exception->getMessage());
            throw new RuntimeException($message, 0, $exception);
        }

        $result = json_decode($response);
        $errorId = intval($result->errorId ?? 0);
        if (0 !== $errorId) {
            throw new RuntimeException(
                sprintf('Anti-Captcha Error (%s): %s', $errorId, strval($result->errorDescription ?? '(unknown)'))
            );
        }

        return $result;
    }
}
