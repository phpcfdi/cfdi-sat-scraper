<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

interface Filters
{
    /**
     * Return the standard inputs affected to be used on the select search type operation
     *
     * @return array<string, string>
     */
    public function getInitialFilters(): array;

    /**
     * Return the standard inputs affected to be used on the search operation (to get the Metadata html content)
     *
     * @return array<string, string>
     */
    public function getRequestFilters(): array;
}
