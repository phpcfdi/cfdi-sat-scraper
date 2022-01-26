<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use OutOfRangeException;
use PhpCfdi\CfdiSatScraper\Contracts\ResourceFileNamerInterface;
use PhpCfdi\CfdiSatScraper\ResourceType;

/**
 * This class is an implementation of ResourceFileNamerInterface to create a file name for a UUID
 * depending on a resource type.
 *
 * @internal
 */
final class ResourceFileNamerByType implements ResourceFileNamerInterface
{
    /** @var ResourceType */
    private $resourceType;

    public function __construct(ResourceType $resourceType)
    {
        $this->resourceType = $resourceType;
    }

    public function getResourceType(): ResourceType
    {
        return $this->resourceType;
    }

    /**
     * @inheritdoc
     */
    public function nameFor(string $uuid): string
    {
        $resourceType = $this->getResourceType();
        if ($resourceType->isXml()) {
            return $uuid . '.xml';
        }
        if ($resourceType->isPdf()) {
            return $uuid . '.pdf';
        }
        if ($resourceType->isCancelRequest()) {
            return $uuid . '-cancel-request.pdf';
        }
        if ($resourceType->isCancelVoucher()) {
            return $uuid . '-cancel-voucher.pdf';
        }
        throw new OutOfRangeException("Don't know how to generate name for resource {$resourceType->value()}");
    }
}
