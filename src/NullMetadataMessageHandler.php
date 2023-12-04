<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use DateTimeImmutable;

class NullMetadataMessageHandler implements Contracts\MetadataMessageHandler
{
    public function resolved(DateTimeImmutable $since, DateTimeImmutable $until, int $count): void
    {
    }

    public function date(DateTimeImmutable $since, DateTimeImmutable $until, int $count): void
    {
    }

    public function divide(DateTimeImmutable $since, DateTimeImmutable $until): void
    {
    }

    public function maximum(DateTimeImmutable $moment): void
    {
    }
}
