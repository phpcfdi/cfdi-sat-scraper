<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

interface DownloadXmlHandlerInterface
{
    /**
     * Invoked when the async request promise was fulfilled
     *
     * @param ResponseInterface $response
     * @param string $uuid
     */
    public function onFulfilled(ResponseInterface $response, string $uuid): void;

    /**
     * Invoked when the promise was rejected and the reason is a RequestException
     *
     * @param RequestException $exception
     * @param string $uuid
     */
    public function onRequestException(RequestException $exception, string $uuid): void;

    /**
     * Invoked when the promise was rejected but the reason is not a RequestException
     *
     * @param mixed $reason
     * @param string $uuid
     */
    public function onRejected($reason, string $uuid): void;
}
