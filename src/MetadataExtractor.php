<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use Symfony\Component\DomCrawler\Crawler;

class MetadataExtractor
{
    public function extract(string $html, ?array $fieldsCaptions = null): array
    {
        if (null === $fieldsCaptions) {
            $fieldsCaptions = $this->defaultFieldsCaptions();
        }

        $rows = (new Crawler($html))->filter('table#ctl00_MainContent_tblResult > tr');

        // first tr is the only expected to have the th elements
        $fieldsPositions = $this->locateFieldsPositions($rows->first(), $fieldsCaptions);

        // slice first row (headers), build data array as a collection of metadata
        $data = $rows->slice(1)->each(
            function (Crawler $row) use ($fieldsPositions): array {
                $metadata = $this->obtainMetadataValues($row, $fieldsPositions);
                $metadata['fechaCancelacion'] = $metadata['fechaProcesoCancelacion'];
                $metadata['urlXml'] = $this->obtainUrlXml($row);
                return $metadata;
            }
        );

        // build metadata using uuid as key
        $data = array_combine(array_column($data, 'uuid'), $data);

        return $data;
    }

    public function defaultFieldsCaptions(): array
    {
        return [
            'uuid' => 'Folio Fiscal',
            'rfcEmisor' => 'RFC Emisor',
            'nombreEmisor' => 'Nombre o Razón Social del Emisor',
            'rfcReceptor' => 'RFC Receptor',
            'nombreReceptor' => 'Nombre o Razón Social del Receptor',
            'fechaEmision' => 'Fecha de Emisión',
            'fechaCertificacion' => 'Fecha de Certificación',
            'pacCertifico' => 'PAC que Certificó',
            'total' => 'Total',
            'efectoComprobante' => 'Efecto del Comprobante',
            'estatusCancelacion' => 'Estatus de cancelación',
            'estadoComprobante' => 'Estado del Comprobante',
            'estatusProcesoCancelacion' => 'Estatus de Proceso de Cancelación',
            'fechaProcesoCancelacion' => 'Fecha de Proceso de Cancelación',
        ];
    }

    public function locateFieldsPositions(Crawler $headersRow, array $fieldsCaptions): array
    {
        $headerCells = $headersRow->children()->each(
            function (Crawler $cell) {
                return trim($cell->text());
            }
        );

        $headerPositions = $fieldsCaptions;
        foreach ($headerPositions as $field => $label) {
            $headerPositions[$field] = array_search($label, $headerCells);
            if (false === $headerPositions[$field]) {
                unset($headerPositions[$field]);
            }
        }

        return $headerPositions;
    }

    public function obtainMetadataValues(Crawler $row, array $fieldsPositions): array
    {
        $values = [];
        $cells = $row->children();
        foreach ($fieldsPositions as $field => $position) {
            $values[$field] = trim($cells->getNode($position)->textContent ?? '');
        }
        return $values;
    }

    public function obtainUrlXml(Crawler $row): ?string
    {
        $spansBtnDownload = $row->filter('span#BtnDescarga');
        if (0 === $spansBtnDownload->count()) { // button not found
            return null;
        }

        $onClickAttribute = $spansBtnDownload->first()->attr('onclick') ?? '';
        if ('' === $onClickAttribute) { // button without text
            return null;
        }

        // change javascript call and replace it with complete url
        return str_replace(
            ["return AccionCfdi('", "','Recuperacion');"],
            [URLS::SAT_URL_PORTAL_CFDI, ''],
            $onClickAttribute
        );
    }
}
