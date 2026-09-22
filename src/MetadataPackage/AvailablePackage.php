<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\MetadataPackage;

use JsonSerializable;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;

/**
 * The AvailablePackage class stores the values of a metadata package that is
 * ready to be downloaded from the SAT portal massive download service.
 */
final readonly class AvailablePackage implements JsonSerializable
{
    private string $uuid;

    /**
     * @throws InvalidArgumentException when UUID is empty
     * @throws InvalidArgumentException when downloadUrl is empty
     */
    public function __construct(
        string $uuid,
        private string $downloadUrl,
    ) {
        if ('' === $uuid) {
            throw InvalidArgumentException::emptyInput('uuid');
        }
        if ('' === $this->downloadUrl) {
            throw InvalidArgumentException::emptyInput('downloadUrl');
        }
        $this->uuid = $uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getDownloadUrl(): string
    {
        return $this->downloadUrl;
    }

    /** @return array<string, string> */
    public function jsonSerialize(): array
    {
        return [
            'uuid' => $this->uuid,
            'downloadUrl' => $this->downloadUrl,
        ];
    }
}
