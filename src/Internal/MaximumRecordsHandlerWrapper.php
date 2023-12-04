<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Contracts\MaximumRecordsHandler;
use PhpCfdi\CfdiSatScraper\NullMetadataMessageHandler;

/**
 * This class wraps a MaximumRecordsHandler into a MetadataMessageHandler.
 * This class will be removed on version 4.0.0
 *
 * @internal
 */
final class MaximumRecordsHandlerWrapper extends NullMetadataMessageHandler
{
    /** @var MaximumRecordsHandler */
    private $maximumRecordsHandler;

    public function __construct(MaximumRecordsHandler $maximumRecordsHandler)
    {
        $this->maximumRecordsHandler = $maximumRecordsHandler;
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function maximum(DateTimeImmutable $moment): void
    {
        $this->maximumRecordsHandler->handle($moment);
    }
}
