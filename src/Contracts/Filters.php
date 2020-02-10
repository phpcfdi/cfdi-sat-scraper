<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

interface Filters
{
    /**
     * @return array<string, string>
     */
    public function getFilters(): array;

    /**
     * @return array<string, string>
     */
    public function getInitialFilters(): array;

    /**
     * @return array<string, string>
     */
    public function getRequestFilters(): array;
}
