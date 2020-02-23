<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

use PhpCfdi\CfdiSatScraper\Filters\DownloadType;

interface QueryInterface
{
    public function getDownloadType(): DownloadType;
}
