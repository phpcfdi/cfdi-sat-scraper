<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use Throwable;

class RuntimeException extends \RuntimeException implements SatException
{
    protected function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function pathDoesNotExists(string $path): self
    {
        return new self(sprintf('The path %s does not exists', $path));
    }

    public static function pathIsNotFolder(string $path): self
    {
        return new self(sprintf('The path %s is not a folder', $path));
    }

    public static function unableToCreateFolder(string $destinationFolder, Throwable $previous = null): self
    {
        return new self(sprintf('Unable to create folder %s', $destinationFolder), $previous);
    }

    public static function unableToFilePutContents(string $destinationFile, Throwable $previous = null): self
    {
        return new self(sprintf('Unable to put contents on %s', $destinationFile), $previous);
    }

    public static function unableToFindCaptchaImage(string $selector): self
    {
        return new self(sprintf("Unable to find image using filter '%s'", $selector));
    }
}
