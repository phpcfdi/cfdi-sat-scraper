<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters\Options;

use PhpCfdi\CfdiSatScraper\Contracts\Filters\FilterOption;

class RfcOption implements FilterOption
{
    /**
     * @var string
     */
    protected $value;

    /**
     * Rfc constructor.
     * @param string $rfc
     */
    public function __construct(string $rfc)
    {
        $this->value = $rfc;
    }

    /**
     * @return string
     */
    public function nameIndex(): string
    {
        return 'ctl00$MainContent$TxtRfcReceptor';
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }
}
