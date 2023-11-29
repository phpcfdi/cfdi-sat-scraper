<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Contracts\MaximumRecordsHandler;
use PhpCfdi\CfdiSatScraper\Contracts\MetadataMessageHandler;

/**
 * This class wraps a MetadataMessageHandler into a MaximumRecordsHandler.
 * This class will be removed on version 4.0.0
 *
 * @internal
 */
final class MetadataMessageHandlerWrapper implements MaximumRecordsHandler
{
    /** @var MetadataMessageHandler */
    private $messageHandler;

    public function __construct(MetadataMessageHandler $messageHandler)
    {
        $this->messageHandler = $messageHandler;
    }

    public function handle(DateTimeImmutable $moment): void
    {
        $this->messageHandler->maximum($moment);
    }
}
