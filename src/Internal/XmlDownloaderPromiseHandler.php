<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use GuzzleHttp\Exception\RequestException;
use PhpCfdi\CfdiSatScraper\Contracts\XmlDownloaderPromiseHandlerInterface;
use PhpCfdi\CfdiSatScraper\Contracts\XmlDownloadHandlerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\XmlDownloadError;
use PhpCfdi\CfdiSatScraper\Exceptions\XmlDownloadRequestExceptionError;
use PhpCfdi\CfdiSatScraper\Exceptions\XmlDownloadResponseError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * This is a handler for \GuzzleHttp\Promise\EachPromise events fulfilled & rejected.
 *
 * It performs basic operations like validate the received response and call "onSuccess" method or
 * call "onError" method with a generic or specialized XmlDownloadError object.
 *
 * @internal
 */
final class XmlDownloaderPromiseHandler implements XmlDownloaderPromiseHandlerInterface
{
    /** @var XmlDownloadHandlerInterface */
    private $handler;

    /** @var string[] */
    private $fulfilledUuids = [];

    public function __construct(XmlDownloadHandlerInterface $handler)
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
        } catch (XmlDownloadResponseError $exception) {
            return $this->handlerError($exception);
        } catch (Throwable $exception) {
            return $this->handlerError(XmlDownloadResponseError::onSuccessException($response, $uuid, $exception));
        }

        $this->fulfilledUuids[] = $uuid;
        return null;
    }

    /**
     * Validate that the Response object was OK and contains something that looks like CFDI.
     * Return the content read from the response body.
     *
     * @param ResponseInterface $response
     * @param string $uuid
     * @return string
     *
     * @throws XmlDownloadResponseError
     */
    public function validateResponse(ResponseInterface $response, string $uuid): string
    {
        if (200 !== $response->getStatusCode()) {
            throw XmlDownloadResponseError::invalidStatusCode($response, $uuid);
        }

        $content = strval($response->getBody());

        if ('' === $content) {
            throw XmlDownloadResponseError::emptyContent($response, $uuid);
        }

        if (false === stripos($content, 'UUID="')) {
            throw XmlDownloadResponseError::contentIsNotCfdi($response, $uuid);
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
            return $this->handlerError(XmlDownloadRequestExceptionError::onRequestException($reason, $uuid));
        }

        return $this->handlerError(XmlDownloadError::onRejected($uuid, $reason));
    }

    /**
     * Send the error to handler error method
     *
     * @param XmlDownloadError $error
     * @return null
     */
    public function handlerError(XmlDownloadError $error)
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

    public function getHandler(): XmlDownloadHandlerInterface
    {
        return $this->handler;
    }
}
