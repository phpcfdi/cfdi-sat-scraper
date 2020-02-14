<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use GuzzleHttp\Exception\RequestException;
use PhpCfdi\CfdiSatScraper\Contracts\DownloadXmlHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * This is an implementation of DownloadXmlHandlerInterface.
 * It performs basic operations like validate the received response
 * or call to specialized method onRequestException.
 *
 * It works as an internal wrapper of the real handler.
 *
 * @internal
 */
final class DownloadXmlMainHandler implements DownloadXmlHandlerInterface
{
    /** @var DownloadXmlHandlerInterface */
    private $handler;

    public function __construct(DownloadXmlHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    public function onFulfilled(ResponseInterface $response, string $uuid): void
    {
        if (200 !== $response->getStatusCode()) {
            throw new RuntimeException(sprintf('Download of CFDI %s return an invalid status code %d', $uuid, $response->getStatusCode()));
        }
        $content = (string) $response->getBody();
        if ('' === $content) {
            throw new RuntimeException(sprintf('Downloaded CFDI %s was empty', $uuid));
        }
        if (false === stripos($content, 'UUID="')) {
            throw new RuntimeException(sprintf('Downloaded CFDI %s is not a CFDI', $uuid));
        }

        $this->handler->onFulfilled($response, $uuid);
    }

    public function onRequestException(RequestException $exception, string $uuid): void
    {
        $this->handler->onRequestException($exception, $uuid);
    }

    public function onRejected($reason, string $uuid): void
    {
        if ($reason instanceof RequestException) {
            $this->onRequestException($reason, $uuid);
            return;
        }
        $this->handler->onRejected($reason, $uuid);
    }
}
