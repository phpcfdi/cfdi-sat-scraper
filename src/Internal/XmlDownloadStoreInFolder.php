<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use PhpCfdi\CfdiSatScraper\Contracts\XmlDownloadHandlerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Exceptions\XmlDownloadError;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * This is a class to perform the XmlDownloader::saveTo method.
 * @see \PhpCfdi\CfdiSatScraper\XmlDownloader::saveTo()
 */
final class XmlDownloadStoreInFolder implements XmlDownloadHandlerInterface
{
    /** @var string */
    private $destinationFolder;

    /**
     * XmlDownloadStoreInFolder constructor.
     *
     * @param string $destinationFolder
     * @throws InvalidArgumentException if destination folder argument is empty
     */
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

    /**
     * This method is invoked from XmlDownloader::saveTo() to validate that the
     * destination folder exists or create it.
     *
     * @param bool $createDestinationFolder
     * @param int $createMode
     * @throws RuntimeException if ask to create destination folder and was an error creating it
     * @throws InvalidArgumentException if don't ask to create destination folder and it does not exists
     * @throws InvalidArgumentException if ask to create destination folder and it exists and is not a foler
     * @throws InvalidArgumentException if ask to create destination folder and it exists and is not a foler
     */
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

    /**
     * @inheritDoc
     * @throws RuntimeException if putting the content into file fails
     */
    public function onSuccess(string $uuid, string $content, ResponseInterface $response): void
    {
        $destinationFile = $this->pathFor($uuid);
        $putContents = file_put_contents($destinationFile, $content);
        if (false === $putContents) { // in case error reporting is disabled
            throw new RuntimeException(sprintf('Unable to save CFDI %s to %s', $uuid, $destinationFile));
        }
    }

    /**
     * @inheritDoc
     */
    public function onError(XmlDownloadError $error): void
    {
        // errors are just ignored
    }
}
