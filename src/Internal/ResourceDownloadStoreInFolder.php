<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use PhpCfdi\CfdiSatScraper\Contracts\ResourceDownloadHandlerInterface;
use PhpCfdi\CfdiSatScraper\Contracts\ResourceFileNamerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadError;
use PhpCfdi\CfdiSatScraper\Exceptions\RuntimeException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * This is a class to perform the ResourceDownloader::saveTo method.
 *
 * @see \PhpCfdi\CfdiSatScraper\ResourceDownloader::saveTo()
 * @internal
 */
final class ResourceDownloadStoreInFolder implements ResourceDownloadHandlerInterface
{
    /** @var string */
    private $destinationFolder;

    /** @var ResourceFileNamerInterface */
    private $resourceFileNamer;

    /**
     * ResourceDownloadStoreInFolder constructor.
     *
     * @param string $destinationFolder
     * @param ResourceFileNamerInterface $resourceFileNamer
     */
    public function __construct(string $destinationFolder, ResourceFileNamerInterface $resourceFileNamer)
    {
        if ('' === $destinationFolder) {
            throw InvalidArgumentException::emptyInput('destination folder');
        }
        $this->destinationFolder = $destinationFolder;
        $this->resourceFileNamer = $resourceFileNamer;
    }

    public function getDestinationFolder(): string
    {
        return $this->destinationFolder;
    }

    public function getResouceFileNamer(): ResourceFileNamerInterface
    {
        return $this->resourceFileNamer;
    }

    public function pathFor(string $uuid): string
    {
        return $this->getDestinationFolder() . DIRECTORY_SEPARATOR . $this->resourceFileNamer->nameFor($uuid);
    }

    /**
     * This method is invoked from ResourceDownloader::saveTo() to validate that the
     * destination folder exists or create it.
     *
     * @param bool $createDestinationFolder
     * @param int $createMode
     *
     * @throws RuntimeException if didn't ask to create folder and path does not exist
     * @throws RuntimeException if ask to create folder path exists and is not a folder
     * @throws RuntimeException if unable to create folder
     */
    public function checkDestinationFolder(bool $createDestinationFolder, int $createMode = 0755): void
    {
        $destinationFolder = $this->getDestinationFolder();

        if (is_dir($destinationFolder)) {
            return;
        }

        if (! $createDestinationFolder) {
            throw RuntimeException::pathDoesNotExists($destinationFolder);
        }

        if (file_exists($destinationFolder)) {
            throw RuntimeException::pathIsNotFolder($destinationFolder);
        }

        $this->mkdirRecursive($destinationFolder, $createMode);
    }

    /**
     * @inheritDoc
     * @throws RuntimeException if putting the content into file fails
     */
    public function onSuccess(string $uuid, string $content, ResponseInterface $response): void
    {
        $destinationFile = $this->pathFor($uuid);
        $this->filePutContents($destinationFile, $content);
    }

    /**
     * @inheritDoc
     */
    public function onError(ResourceDownloadError $error): void
    {
        // errors are just ignored
    }

    /**
     * @param string $destinationFolder
     * @param int $createMode
     * @throws RuntimeException if unable to create folder
     */
    public function mkdirRecursive(string $destinationFolder, int $createMode): void
    {
        try {
            $mkdir = mkdir($destinationFolder, $createMode, true);
        } catch (Throwable $exception) {
            throw RuntimeException::unableToCreateFolder($destinationFolder, $exception);
        }
        if (false === $mkdir) { // in case error reporting is disabled
            throw RuntimeException::unableToCreateFolder($destinationFolder);
        }
    }

    /**
     * @param string $destinationFile
     * @param string $content
     * @throws RuntimeException if unable to put contents on file
     */
    public function filePutContents(string $destinationFile, string $content): void
    {
        try {
            $putContents = file_put_contents($destinationFile, $content);
        } catch (Throwable $exception) {
            throw RuntimeException::unableToFilePutContents($destinationFile, $exception);
        }
        if (false === $putContents) { // in case error reporting is disabled
            throw RuntimeException::unableToFilePutContents($destinationFile);
        }
    }
}
