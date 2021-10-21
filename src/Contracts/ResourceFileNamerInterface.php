<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

interface ResourceFileNamerInterface
{
    /**
     * A class that implements this interface should return the file name to store a specific uuid by resource type
     *
     * @param string $uuid
     * @return string
     */
    public function nameFor(string $uuid): string;
}
