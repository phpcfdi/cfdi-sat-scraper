<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use Throwable;

/**
 * This is a base exception to track a client exception or an exception with a response.
 * This exception must be thrown by SatHttpGateway.
 *
 * @see \PhpCfdi\CfdiSatScraper\SatHttpGateway
 * @see SatHttpGatewayClientException
 * @see SatHttpGatewayResponseException
 */
abstract class SatHttpGatewayException extends \RuntimeException implements SatException
{
    /** @var string */
    private $url;

    /** @var array<string, mixed> */
    private $requestHeaders;

    /** @var array<string, mixed> */
    private $requestData;

    /** @var string */
    private $httpMethod;

    /**
     * SatHttpGatewayException constructor.
     *
     * @param string $message
     * @param string $httpMethod
     * @param string $url
     * @param array<string, mixed> $requestHeaders
     * @param array<string, mixed> $requestData
     * @param Throwable|null $previous
     */
    protected function __construct(
        string $message,
        string $httpMethod,
        string $url,
        array $requestHeaders,
        array $requestData = [],
        Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->httpMethod = $httpMethod;
        $this->url = $url;
        $this->requestHeaders = $requestHeaders;
        $this->requestData = $requestData;
    }

    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /** @return array<string, mixed> */
    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }

    /** @return array<string, mixed> */
    public function getRequestData(): array
    {
        return $this->requestData;
    }
}
