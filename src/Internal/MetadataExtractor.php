<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use PhpCfdi\CfdiSatScraper\Metadata;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\URLS;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

/**
 * Parses a web page to obtain all the Metadata records on it.
 *
 * @internal
 */
class MetadataExtractor
{
    /**
     * @param array<string, string>|null $fieldsCaptions
     */
    public function extract(string $html, ?array $fieldsCaptions = null): MetadataList
    {
        if (null === $fieldsCaptions) {
            $fieldsCaptions = $this->defaultFieldsCaptions();
        }

        try {
            $rows = (new Crawler($html))->filter('table#ctl00_MainContent_tblResult > tr');
        } catch (RuntimeException) {
            return new MetadataList([]);
        }
        if ($rows->count() < 2) {
            return new MetadataList([]);
        }

        // first row is the only expected to have the th elements
        $fieldsPositions = $this->locateFieldsPositions($rows->first(), $fieldsCaptions);

        // slice first row (headers), build data array as a collection of metadata
        $data = $rows->slice(1)->each(
            function (Crawler $row) use ($fieldsPositions): ?Metadata {
                $metadata = $this->obtainMetadataValues($row, $fieldsPositions);
                if ('' === ($metadata['uuid'] ?? '')) {
                    return null;
                }
                $metadata[ResourceType::xml()->value()] = $this->obtainUrlXml($row);
                $metadata[ResourceType::pdf()->value()] = $this->obtainUrlPdf($row);
                $metadata[ResourceType::cancelRequest()->value()] = $this->obtainUrlCancelRequest($row);
                $metadata[ResourceType::cancelVoucher()->value()] = $this->obtainUrlCancelVoucher($row);
                return new Metadata($metadata['uuid'], $metadata);
            },
        );

        // build metadata using uuid as key
        return new MetadataList($data);
    }

    /**
     * @return array<string, string>
     * @see Metadata
     */
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
            'fechaSolicitudCancelacion' => 'Fecha de Solicitud de la Cancelación',
            'fechaDeCancelacion' => 'Fecha de Cancelación',
            'rfcACuentaTerceros' => 'RFC a cuenta de terceros',
            'motivoCancelacion' => 'Motivo',
            'folioSustitucion' => 'Folio de Sustitución',
        ];
    }

    /**
     * @param array<string, string> $fieldsCaptions
     * @return array<string, int>
     */
    public function locateFieldsPositions(Crawler $headersRow, array $fieldsCaptions): array
    {
        try {
            /** @var array<int, string> $headerCells */
            $headerCells = $headersRow->children()->each(
                fn (Crawler $cell): string => trim($cell->text()),
            );
        } catch (RuntimeException) {
            return [];
        }

        $headerPositions = [];
        foreach ($fieldsCaptions as $field => $label) {
            /** @var int|false $search */
            $search = array_search($label, $headerCells);
            if (false !== $search) {
                $headerPositions[$field] = $search;
            }
        }

        return $headerPositions;
    }

    /**
     * @param array<string, int> $fieldsPositions
     * @return array<string, string>
     */
    public function obtainMetadataValues(Crawler $row, array $fieldsPositions): array
    {
        try {
            $cells = $row->children();
        } catch (RuntimeException) {
            return [];
        }

        return array_map(
            fn (int $position): string => trim($cells->getNode($position)->textContent ?? ''),
            $fieldsPositions,
        );
    }

    public function obtainUrlXml(Crawler $row): string
    {
        $onClickAttribute = $this->obtainOnClickFromElement($row, 'span#BtnDescarga');
        return str_replace(
            ["return AccionCfdi('", "','Recuperacion');"],
            [URLS::PORTAL_CFDI, ''],
            $onClickAttribute,
        );
    }

    public function obtainUrlPdf(Crawler $row): string
    {
        $onClickAttribute = $this->obtainOnClickFromElement($row, 'span#BtnRI');
        return str_replace(
            ["recuperaRepresentacionImpresa('", "');"],
            [URLS::PORTAL_CFDI . 'RepresentacionImpresa.aspx?Datos=', ''],
            $onClickAttribute,
        );
    }

    public function obtainUrlCancelRequest(Crawler $row): string
    {
        $onClickAttribute = $this->obtainOnClickFromElement($row, 'span#BtnRecuperaAcuse');
        return str_replace(
            ["AccionCfdi('", "','Acuse');"],
            [URLS::PORTAL_CFDI, ''],
            $onClickAttribute,
        );
    }

    public function obtainUrlCancelVoucher(Crawler $row): string
    {
        $onClickAttribute = $this->obtainOnClickFromElement($row, 'span#BtnRecuperaAcuseFinal');
        // change javascript call and replace it with complete url
        return str_replace(
            ["javascript:window.location.href='", "';"],
            [URLS::PORTAL_CFDI, ''],
            $onClickAttribute,
        );
    }

    private function obtainOnClickFromElement(Crawler $crawler, string $elementFilter): string
    {
        try {
            $filteredElements = $crawler->filter($elementFilter);
        } catch (Throwable) {
            return '';
        }

        if (0 === $filteredElements->count()) { // button not found
            return '';
        }

        return $filteredElements->first()->attr('onclick') ?? '';
    }
}
