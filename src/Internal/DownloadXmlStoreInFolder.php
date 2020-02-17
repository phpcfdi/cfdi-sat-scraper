<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use GuzzleHttp\Exception\RequestException;
use PhpCfdi\CfdiSatScraper\Contracts\DownloadXmlHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

class DownloadXmlStoreInFolder implements DownloadXmlHandlerInterface
{
    /**
     * @var string
     */
    private $destinationFolder;

    /**
     * Helper class
     *
     * @param string $destinationFolder
     */
    public function __construct(string $destinationFolder)
    {
        $this->destinationFolder = $destinationFolder;
    }

    public function getDestinationFolder(): string
    {
        return $this->destinationFolder;
    }

    public function pathFor(string $uuid): string
    {
        return $this->destinationFolder . DIRECTORY_SEPARATOR . $uuid . '.xml';
    }

    public function checkDestinationFolder(bool $createDestinationFolder, int $createMode = 0755): void
    {
        if (! $createDestinationFolder) {
            if (! is_dir($this->destinationFolder)) {
                throw new RuntimeException("The provided path [{$this->destinationFolder}] not exists");
            }
            return;
        }

        if (is_dir($this->destinationFolder)) {
            return;
        }

        if (file_exists($this->destinationFolder)) {
            throw new RuntimeException(
                "The provided path [{$this->destinationFolder}] already exists and is not a directory"
            );
        }

        mkdir($this->destinationFolder, $createMode, true);
    }

    public function onFulfilled(ResponseInterface $response, string $uuid): void
    {
        $destinationFile = $this->pathFor($uuid);
        $putContents = file_put_contents($destinationFile, (string) $response->getBody());
        if (false === $putContents) {
            throw new RuntimeException(sprintf('Unable to save CFDI %s to %s', $uuid, $destinationFile));
        }
    }

    public function onRequestException(RequestException $exception, string $uuid): void
    {
        throw new RuntimeException(
            sprintf('Unable to retrieve CFDI %s from %s', $uuid, (string) $exception->getRequest()->getUri()),
            0,
            $exception
        );
    }

    public function onRejected($reason, string $uuid): void
    {
        if ($reason instanceof Throwable) {
            // TODO: this should contain an exception that is able to contain the reason
            $reason = new RuntimeException(sprintf('Unable to retrieve CFDI %s, reason: %s', $uuid, strval($reason)));
        }
        throw $reason;
    }
}
