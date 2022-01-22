<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Inputs;

use PhpCfdi\CfdiSatScraper\QueryByUuid;

/**
 * @extends InputsGeneric<QueryByUuid>
 */
class InputsByUuid extends InputsGeneric implements InputsInterface
{
    public function __construct(QueryByUuid $query)
    {
        parent::__construct($query);
    }

    public function getCentralFilter(): string
    {
        return 'RdoFolioFiscal';
    }

    public function getFilterOptions(): array
    {
        return [$this->getQuery()->getUuid()];
    }
}
