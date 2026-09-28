<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use Throwable;

class PackageIsNotAvailableException extends MetadataPackageException
{
    private string $uuid;

    public function __construct(string $uuid, ?Throwable $previous = null)
    {
        parent::__construct(
            message: sprintf('The package with UUID %s is not available for download', $uuid),
            previous: $previous,
        );
        $this->uuid = $uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
