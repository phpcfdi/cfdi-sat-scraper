<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Inputs;

use PhpCfdi\CfdiSatScraper\Contracts\FilterOption;
use PhpCfdi\CfdiSatScraper\Contracts\QueryInterface;

interface InputsInterface
{
    /**
     * Return the query property
     *
     * @return QueryInterface
     */
    public function getQuery(): QueryInterface;

    /**
     * Return an array of FilterOption related to the current query
     *
     * @return FilterOption[]
     */
    public function getFilterOptions(): array;

    /**
     * Contains the central filter id
     *
     * @return string
     */
    public function getCentralFilter(): string;

    /**
     * Return only the appropriate key-values to override based on the query
     *
     * @return array<string, string>
     */
    public function getQueryAsInputs(): array;

    /**
     * Return the minimum set of inputs to make an ajax request
     *
     * @return array<string, string>
     */
    public function getAjaxInputs(): array;

    /**
     * Return the URL which all http transactions will be sent
     *
     * @return string
     */
    public function getUrl(): string;
}
