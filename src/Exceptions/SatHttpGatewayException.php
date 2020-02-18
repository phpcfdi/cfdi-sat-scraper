<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class SatHttpGatewayException extends RuntimeException implements SatException
{
    /** @var string */
    private $url;

    /** @var ResponseInterface */
    private $response;

    /** @var array<string, string> */
    private $requestFormParams;

    private function __construct(string $message, string $url, ResponseInterface $response, array $requestFormParams = [])
    {
        parent::__construct($message);
        $this->url = $url;
        $this->response = $response;
        $this->requestFormParams = $requestFormParams;
    }

    public static function unexpectedEmptyResponse(string $when, string $url, ResponseInterface $response, array $requestFormParams = []): self
    {
        return new self("Unexpected empty content when $when", $url, $response, $requestFormParams);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /** @return array<string, string> */
    public function getRequestFormParams(): array
    {
        return $this->requestFormParams;
    }
}