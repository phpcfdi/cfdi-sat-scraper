<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use GuzzleHttp\Exception\GuzzleException;

/**
 * This exception is thrown by SatHttpGateway and stores an http client exception GuzzleException
 *
 * @see GuzzleException
 */
class SatHttpGatewayClientException extends SatHttpGatewayException implements SatException
{
    private readonly GuzzleException $clientException;

    /**
     * SatHttpGatewayClientException constructor.
     *
     * @param array<string, mixed> $requestHeaders
     * @param array<string, mixed> $requestData
     */
    protected function __construct(
        string $message,
        string $httpMethod,
        string $url,
        array $requestHeaders,
        array $requestData,
        GuzzleException $previous
    ) {
        parent::__construct($message, $httpMethod, $url, $requestHeaders, $requestData, $previous);
        $this->clientException = $previous;
    }

    /**
     * Method factory
     *
     * @param array<string, mixed> $requestHeaders
     * @param array<string, mixed> $requestData
     */
    public static function clientException(string $when, string $method, string $url, array $requestHeaders, array $requestData, GuzzleException $exception): self
    {
        return new self("HTTP client error when $when", $method, $url, $requestHeaders, $requestData, $exception);
    }

    public function getClientException(): GuzzleException
    {
        return $this->clientException;
    }
}
