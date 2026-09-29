<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Internal;

use RuntimeException;

/**
 * Utility class to manage temporary files that are removed when the instance is destroyed.
 *
 * @internal
 */
final class TemporaryFile
{
    private string $path;

    public function __construct(string $prefix = '', string $directory = '')
    {
        $tempnam = tempnam($directory, $prefix);
        if (false === $tempnam) {
            throw new RuntimeException('Unable to create temporary file');
        }
        $this->path = $tempnam;
    }

    public function __destruct()
    {
        if (file_exists($this->path)) {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            @unlink($this->path);
        }
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getContents(): string
    {
        return file_get_contents($this->path) ?: '';
    }

    public function putContents(string $data): void
    {
        if (false === file_put_contents($this->path, $data)) {
            throw new RuntimeException('Unable to write temporary file');
        }
    }
}
