<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use finfo;
use GuzzleHttp\Exception\RequestException;
use PhpCfdi\CfdiSatScraper\Contracts\ResourceDownloaderPromiseHandlerInterface;
use PhpCfdi\CfdiSatScraper\Contracts\ResourceDownloadHandlerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadError;
use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadRequestExceptionError;
use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadResponseError;
use PhpCfdi\CfdiSatScraper\ResourceType;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * This is a handler for \GuzzleHttp\Promise\EachPromise events fulfilled & rejected.
 *
 * It performs basic operations like validate the received response and call "onSuccess" method or
 * call "onError" method with a generic or specialized ResourceDownloadError object.
 *
 * @internal
 */
final class ResourceDownloaderPromiseHandler implements ResourceDownloaderPromiseHandlerInterface
{
    /** @var ResourceType */
    private $resourceType;

    /** @var ResourceDownloadHandlerInterface */
    private $handler;

    /** @var string[] */
    private $fulfilledUuids = [];

    public function __construct(ResourceType $resourceType, ResourceDownloadHandlerInterface $handler)
    {
        $this->resourceType = $resourceType;
        $this->handler = $handler;
    }

    /**
     * This method handles each promise fulfilled event
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
        } catch (ResourceDownloadResponseError $exception) {
            return $this->handlerError($exception);
        } catch (Throwable $exception) {
            return $this->handlerError(ResourceDownloadResponseError::onSuccessException($response, $uuid, $exception));
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
     * @throws ResourceDownloadResponseError
     */
    public function validateResponse(ResponseInterface $response, string $uuid): string
    {
        if (200 !== $response->getStatusCode()) {
            throw ResourceDownloadResponseError::invalidStatusCode($response, $uuid);
        }

        $content = strval($response->getBody());

        if ('' === $content) {
            throw ResourceDownloadResponseError::emptyContent($response, $uuid);
        }

        if ($this->resourceType->fileTypeIsXml()) {
            if (false === stripos($content, 'UUID="')) {
                throw ResourceDownloadResponseError::contentIsNotCfdi($response, $uuid);
            }
        } elseif ($this->resourceType->fileTypeIsPdf()) {
            $mimeType = strtolower((new finfo())->buffer($content, FILEINFO_MIME_TYPE) ?: '');
            if ('application/pdf' !== $mimeType) {
                throw ResourceDownloadResponseError::contentIsNotPdf($response, $uuid, $mimeType);
            }
        }

        return $content;
    }

    /**
     * This method handles each promise rejected event
     *
     * @param mixed $reason
     * @param string $uuid
     * @return null
     */
    public function promiseRejected($reason, string $uuid)
    {
        if ($reason instanceof RequestException) {
            return $this->handlerError(ResourceDownloadRequestExceptionError::onRequestException($reason, $uuid));
        }

        return $this->handlerError(ResourceDownloadError::onRejected($uuid, $reason));
    }

    /**
     * Send the error to handler error method
     *
     * @param ResourceDownloadError $error
     * @return null
     */
    public function handlerError(ResourceDownloadError $error)
    {
        $this->handler->onError($error);
        return null;
    }

    /**
     * Return the list of successfully processed UUIDS
     *
     * @return string[]
     */
    public function downloadedUuids(): array
    {
        return $this->fulfilledUuids;
    }

    public function getResourceType(): ResourceType
    {
        return $this->resourceType;
    }

    public function getHandler(): ResourceDownloadHandlerInterface
    {
        return $this->handler;
    }
}
