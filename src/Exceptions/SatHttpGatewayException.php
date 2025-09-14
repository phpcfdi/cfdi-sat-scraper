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
    /**
     * @param array<string, mixed> $requestHeaders
     * @param array<string, mixed> $requestData
     */
    protected function __construct(
        string $message,
        private readonly string $httpMethod,
        private readonly string $url,
        private readonly array $requestHeaders,
        private readonly array $requestData = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
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
