<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

interface Filters
{
    /**
     * @return mixed
     */
    public function getFilters(): array;

    /**
     * @return mixed
     */
    public function getInitialFilters(): array;

    /**
     * @return mixed
     */
    public function getRequestFilters(): array;
}
