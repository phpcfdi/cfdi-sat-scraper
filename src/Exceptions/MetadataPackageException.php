<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use Throwable;

class MetadataPackageException extends RuntimeException
{
    public static function searchHasNoResults(): self
    {
        return new self('The search did not return results');
    }

    public static function requestRejected(string $portalMessage): self
    {
        return new self(sprintf('The portal rejected the metadata package request: %s', $portalMessage));
    }

    public static function unableToObtainFolio(string $portalMessage): self
    {
        return new self(sprintf('Unable to obtain the folio from the portal response: %s', $portalMessage));
    }

    public static function invalidPackageContents(string $uuid, string $contents): self
    {
        $excerpt = trim(substr(strip_tags($contents), 0, 200));
        return new self(sprintf(
            'The portal did not return a ZIP package for folio %s. Response excerpt: %s',
            $uuid,
            '' !== $excerpt ? $excerpt : '(empty or binary)',
        ));
    }

    public static function unableToWritePackage(string $destinationPath, ?Throwable $previous = null): self
    {
        return new self(sprintf('Unable to write the package file %s', $destinationPath), $previous);
    }
}
