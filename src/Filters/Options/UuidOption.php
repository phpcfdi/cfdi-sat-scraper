<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters\Options;

use PhpCfdi\CfdiSatScraper\Contracts\FilterOption;

/**
 * UUID option
 */
class UuidOption implements FilterOption
{
    /** @var string */
    protected $value;

    public function __construct(string $uuid)
    {
        $this->value = strtolower($uuid);
    }

    public function nameIndex(): string
    {
        return 'ctl00$MainContent$TxtUUID';
    }

    public function value(): string
    {
        return $this->value;
    }
}
