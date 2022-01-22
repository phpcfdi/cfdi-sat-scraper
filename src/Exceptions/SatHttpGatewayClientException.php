<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use GuzzleHttp\Exception\GuzzleException;

/**
 * This exception is thrown by SatHttpGateway and stores a http client exception GuzzleException
 *
 * @see GuzzleException
 */
class SatHttpGatewayClientException extends SatHttpGatewayException implements SatException
{
    /** @var GuzzleException */
    private $clientException;

    /**
     * SatHttpGatewayClientException constructor.
     *
     * @param string $message
     * @param string $httpMethod
     * @param string $url
     * @param array<string, mixed> $requestHeaders
     * @param array<string, mixed> $requestData
     * @param GuzzleException $previous
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
     * @param string $when
     * @param string $method
     * @param string $url
     * @param array<string, mixed> $requestHeaders
     * @param array<string, mixed> $requestData
     * @param GuzzleException $exception
     * @return self
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
