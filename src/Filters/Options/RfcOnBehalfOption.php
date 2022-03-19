<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters\Options;

use PhpCfdi\CfdiSatScraper\Contracts\FilterOption;

/**
 * RfcOnBehalfOption (A cuenta de terceros) option
 */
class RfcOnBehalfOption implements FilterOption
{
    /** @var string */
    protected $value;

    public function __construct(string $rfc)
    {
        $this->value = mb_strtoupper($rfc);
    }

    public function nameIndex(): string
    {
        return 'ctl00$MainContent$TxtRfcTercero';
    }

    public function value(): string
    {
        return $this->value;
    }
}
