<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use PhpCfdi\CfdiSatScraper\Contracts\XmlDownloadHandlerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Exceptions\XmlDownloadError;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class XmlDownloadStoreInFolder implements XmlDownloadHandlerInterface
{
    /** @var string */
    private $destinationFolder;

    public function __construct(string $destinationFolder)
    {
        if ('' === $destinationFolder) {
            throw InvalidArgumentException::emptyInput('destination folder');
        }
        $this->destinationFolder = $destinationFolder;
    }

    public function getDestinationFolder(): string
    {
        return $this->destinationFolder;
    }

    public function pathFor(string $uuid): string
    {
        return $this->getDestinationFolder() . DIRECTORY_SEPARATOR . $uuid . '.xml';
    }

    public function checkDestinationFolder(bool $createDestinationFolder, int $createMode = 0755): void
    {
        $destinationFolder = $this->getDestinationFolder();
        if (! $createDestinationFolder) {
            if (! is_dir($destinationFolder)) {
                throw InvalidArgumentException::pathDoesNotExists('destination folder', $destinationFolder);
            }
            return;
        }

        if (is_dir($destinationFolder)) {
            return;
        }

        if (file_exists($destinationFolder)) {
            throw InvalidArgumentException::pathIsNotFolder('destination folder', $destinationFolder);
        }

        $mkdir = mkdir($destinationFolder, $createMode, true);
        if (false === $mkdir) { // in case error reporting is disabled
            throw new RuntimeException(sprintf('Unable to create folder %s', $destinationFolder));
        }
    }

    public function onSuccess(string $uuid, string $content, ResponseInterface $response): void
    {
        $destinationFile = $this->pathFor($uuid);
        $putContents = file_put_contents($destinationFile, $content);
        if (false === $putContents) { // in case error reporting is disabled
            throw new RuntimeException(sprintf('Unable to save CFDI %s to %s', $uuid, $destinationFile));
        }
    }

    public function onError(XmlDownloadError $error): void
    {
        // errors are just ignored
    }
}
