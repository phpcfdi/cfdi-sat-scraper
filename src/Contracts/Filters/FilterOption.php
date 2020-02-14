<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts\Filters;

interface FilterOption
{
    /**
     * Returns the input name of the filter
     * @return string
     */
    public function nameIndex(): string;

    /**
     * Return the input value of the filter
     * @return string
     */
    public function value(): string;
}
