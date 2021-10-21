<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Inputs;

use PhpCfdi\CfdiSatScraper\Contracts\QueryInterface;

/**
 * @template TQuery of QueryInterface
 */
abstract class InputsGeneric implements InputsInterface
{
    /** @var TQuery */
    private $query;

    /** @param TQuery $query */
    public function __construct(QueryInterface $query)
    {
        $this->query = $query;
    }

    /** @return TQuery */
    public function getQuery(): QueryInterface
    {
        return $this->query;
    }

    public function getUrl(): string
    {
        return $this->getQuery()->getDownloadType()->url();
    }

    public function getQueryAsInputs(): array
    {
        $inputs = ['ctl00$MainContent$BtnBusqueda' => 'Buscar CFDI'];

        $filters = $this->getFilterOptions();
        foreach ($filters as $filter) {
            $inputs[$filter->nameIndex()] = $filter->value();
        }

        return $inputs;
    }

    public function getAjaxInputs(): array
    {
        $centralFilter = $this->getCentralFilter();
        return [
            '__ASYNCPOST' => 'true',
            '__EVENTARGUMENT' => '',
            '__EVENTTARGET' => 'ctl00$MainContent$' . $centralFilter,
            '__LASTFOCUS' => '',
            'ctl00$MainContent$FiltroCentral' => $centralFilter,
            'ctl00$ScriptManager1' => 'ctl00$MainContent$UpnlBusqueda|ctl00$MainContent$' . $centralFilter,
        ];
    }
}
