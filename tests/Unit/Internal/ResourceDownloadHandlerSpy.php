<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Internal;

use Exception;
use PhpCfdi\CfdiSatScraper\Contracts\ResourceDownloadHandlerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadError;
use Psr\Http\Message\ResponseInterface;

final class ResourceDownloadHandlerSpy implements ResourceDownloadHandlerInterface
{
    public string $lastUuid;

    public string $lastContent;

    public ResponseInterface $lastResponse;

    public ResourceDownloadError $lastError;

    public Exception|null $onSuccessException = null;

    public function onSuccess(string $uuid, string $content, ResponseInterface $response): void
    {
        if (null !== $this->onSuccessException) {
            throw $this->onSuccessException;
        }
        $this->lastUuid = $uuid;
        $this->lastContent = $content;
        $this->lastResponse = $response;
    }

    public function onError(ResourceDownloadError $error): void
    {
        $this->lastError = $error;
    }
}
