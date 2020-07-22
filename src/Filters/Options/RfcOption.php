<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters\Options;

use PhpCfdi\CfdiSatScraper\Contracts\FilterOption;

/**
 * RFC Option
 */
class RfcOption implements FilterOption
{
    /** @var string */
    protected $value;

    public function __construct(string $rfc)
    {
        $this->value = $rfc;
    }

    public function nameIndex(): string
    {
        return 'ctl00$MainContent$TxtRfcReceptor';
    }

    public function value(): string
    {
        return $this->value;
    }
}
