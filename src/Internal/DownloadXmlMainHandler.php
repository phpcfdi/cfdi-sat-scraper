<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use GuzzleHttp\Exception\RequestException;
use PhpCfdi\CfdiSatScraper\Contracts\DownloadXmlHandlerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\DownloadXmlError;
use PhpCfdi\CfdiSatScraper\Exceptions\DownloadXmlRequestExceptionError;
use PhpCfdi\CfdiSatScraper\Exceptions\DownloadXmlResponseError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * This is a handler for \GuzzleHttp\Promise\EachPromise events fulfilled & rejected.
 *
 * It performs basic operations like validate the received response and call "onSuccess" method or
 * call "onError" method with a generic or specialized DownloadXmlError object.
 *
 * @internal
 */
final class DownloadXmlMainHandler
{
    /** @var DownloadXmlHandlerInterface */
    private $handler;

    /** @var string[] */
    private $fulfilledUuids = [];

    public function __construct(DownloadXmlHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * This method handles the each promise fulfilled event
     *
     * @param ResponseInterface $response
     * @param string $uuid
     * @return null
     */
    public function promiseFulfilled(ResponseInterface $response, string $uuid)
    {
        try {
            $content = $this->validateResponse($response, $uuid);
            $this->handler->onSuccess($uuid, $content, $response);
        } catch (DownloadXmlResponseError $exception) {
            return $this->handlerError($exception);
        } catch (Throwable $exception) {
            return $this->handlerError(DownloadXmlResponseError::onSuccess($response, $uuid, $exception));
        }

        $this->fulfilledUuids[] = $uuid;
        return null;
    }

    public function validateResponse(ResponseInterface $response, string $uuid): string
    {
        if (200 !== $response->getStatusCode()) {
            throw DownloadXmlResponseError::invalidStatusCode($response, $uuid);
        }

        $content = (string) $response->getBody();

        if ('' === $content) {
            throw DownloadXmlResponseError::emptyContent($response, $uuid);
        }

        if (false === stripos($content, 'UUID="')) {
            throw DownloadXmlResponseError::contentIsNotCfdi($response, $uuid);
        }

        return $content;
    }

    /**
     * This method handles the each promise rejected event
     *
     * @param mixed $reason
     * @param string $uuid
     * @return null
     */
    public function promiseRejected($reason, string $uuid)
    {
        if ($reason instanceof RequestException) {
            return $this->handlerError(DownloadXmlRequestExceptionError::onRequestException($reason, $uuid));
        }

        return $this->handlerError(DownloadXmlError::onRejected($uuid, $reason));
    }

    /**
     * Send the error to handler error method
     *
     * @param DownloadXmlError $error
     * @return null
     */
    public function handlerError(DownloadXmlError $error)
    {
        $this->handler->onError($error);
        return null;
    }

    /**
     * Return the list of succesfully processed UUIDS
     *
     * @return string[]
     */
    public function downloadedUuids(): array
    {
        return $this->fulfilledUuids;
    }
}
