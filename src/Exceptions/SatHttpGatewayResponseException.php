<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use Psr\Http\Message\ResponseInterface;

/**
 * This exception is thrown by SatHttpGateway when a ResponseInterface exists but an error was found
 *
 * @see GuzzleException
 */
class SatHttpGatewayResponseException extends SatHttpGatewayException implements SatException
{
    /** @var ResponseInterface */
    private $response;

    /**
     * SatHttpGatewayResponseException constructor.
     *
     * @param ResponseInterface $response
     * @param string $message
     * @param string $httpMethod
     * @param string $url
     * @param array<string, mixed> $requestHeaders
     * @param array<string, mixed> $requestData
     */
    protected function __construct(ResponseInterface $response, string $message, string $httpMethod, string $url, array $requestHeaders, array $requestData)
    {
        parent::__construct($message, $httpMethod, $url, $requestHeaders, $requestData);
        $this->response = $response;
    }

    /**
     * Method factory
     *
     * @param string $when
     * @param ResponseInterface $response
     * @param string $httpMethod
     * @param string $url
     * @param array<string, mixed> $requestHeaders
     * @param array<string, mixed> $requestData
     * @return self
     */
    public static function unexpectedEmptyResponse(string $when, ResponseInterface $response, string $httpMethod, string $url, array $requestHeaders, array $requestData = []): self
    {
        return new self($response, "Unexpected empty content when $when", $httpMethod, $url, $requestHeaders, $requestData);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
