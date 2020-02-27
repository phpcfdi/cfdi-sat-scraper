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

    protected function __construct(ResponseInterface $response, string $message, string $httpMethod, string $url, array $requestHeaders, array $requestData)
    {
        parent::__construct($message, $httpMethod, $url, $requestHeaders, $requestData);
        $this->response = $response;
    }

    public static function unexpectedEmptyResponse(string $when, ResponseInterface $response, string $httpMethod, string $url, array $requestHeaders, array $requestData = []): self
    {
        return new self($response, "Unexpected empty content when $when", $httpMethod, $url, $requestHeaders, $requestData);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
