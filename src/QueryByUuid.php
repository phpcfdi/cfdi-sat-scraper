<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Contracts\QueryInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Filters\Options\UuidOption;
use PhpCfdi\CfdiSatScraper\Internal\DownloadTypePropertyTrait;

/**
 * This class stores all the data to perform a search by UUID
 */
class QueryByUuid implements QueryInterface
{
    use DownloadTypePropertyTrait;

    /** @var UuidOption */
    private $uuid;

    public function __construct(UuidOption $uuid, ?DownloadType $downloadType = null)
    {
        $this->setDownloadType($this->getDefaultDownloadType($downloadType));
        $this->setUuid($uuid);
    }

    /**
     * @param UuidOption $uuid
     * @return $this
     */
    final public function setUuid(UuidOption $uuid): self
    {
        if ('' === $uuid->value()) {
            throw InvalidArgumentException::emptyInput('UUID');
        }
        $this->uuid = $uuid;

        return $this;
    }

    final public function getUuid(): UuidOption
    {
        return $this->uuid;
    }
}
