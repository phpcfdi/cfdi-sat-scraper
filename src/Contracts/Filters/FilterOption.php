<?php
declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts\Filters;

interface FilterOption
{
    /**
     * @return string
     */
    public function nameIndex(): string;

    /**
     * @return string
     */
    public function value(): string;
}
