<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use DateTimeImmutable;

class NullMetadataMessageHandler implements Contracts\MetadataMessageHandler
{
    public function resolved(DateTimeImmutable $since, DateTimeImmutable $until, int $count): void
    {
        // null object, do nothing
    }

    public function date(DateTimeImmutable $since, DateTimeImmutable $until, int $count): void
    {
        // null object, do nothing
    }

    public function divide(DateTimeImmutable $since, DateTimeImmutable $until): void
    {
        // null object, do nothing
    }

    public function maximum(DateTimeImmutable $moment): void
    {
        // null object, do nothing
    }
}
