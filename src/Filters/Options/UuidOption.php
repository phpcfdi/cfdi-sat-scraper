<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters\Options;

use PhpCfdi\CfdiSatScraper\Contracts\Filters\FilterOption;

class UuidOption implements FilterOption
{
    /**
     * @var string
     */
    protected $value;

    /**
     * RfcReceptor constructor.
     * @param string $uuid
     */
    public function __construct(string $uuid)
    {
        $this->value = $uuid;
    }

    /**
     * @return string
     */
    public function nameIndex(): string
    {
        return 'ctl00$MainContent$TxtUUID';
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }
}
