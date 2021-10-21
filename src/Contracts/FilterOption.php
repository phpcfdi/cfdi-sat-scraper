<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

/**
 * This interface defines a class that has a name and value describing an option to be sent to get the list of uuids
 * on the html post form.
 */
interface FilterOption
{
    /**
     * Returns the input name of the filter
     *
     * @return string
     */
    public function nameIndex(): string;

    /**
     * Return the input value of the filter
     *
     * @return string
     */
    public function value(): string;
}
