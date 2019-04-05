<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use Symfony\Component\DomCrawler\Crawler;

class MetadataExtractor
{
    /**
     * @param string $html
     * @return array
     */
    public function extract(string $html): array
    {
        $crawler = new Crawler($html);

        // build data array as a collection of metadata
        $data = $crawler
            ->filter('table#ctl00_MainContent_tblResult > tr')
            ->reduce(
                function (Crawler $row) {
                    return ('td' === $row->children()->first()->nodeName());
                }
            )->each(
                function (Crawler $row): array {
                    $metadata = $this->obtainMetadataValues($row);
                    $metadata['fechaCancelacion'] = $metadata['fechaProcesoCancelacion'];
                    $metadata['urlXml'] = $this->obtainUrlXml($row);
                    return $metadata;
                }
            );

        // build metadata using uuid as key
        $data = array_combine(array_column($data, 'uuid'), $data);

        return $data;
    }

    public function obtainMetadataValues(Crawler $row): array
    {
        $cells = $row->filter('td[style="WORD-BREAK:BREAK-ALL;"]');

        return [
            'uuid' => trim($cells->getNode(0)->textContent ?? ''),
            'rfcEmisor' => trim($cells->getNode(1)->textContent ?? ''),
            'nombreEmisor' => trim($cells->getNode(2)->textContent ?? ''),
            'rfcReceptor' => trim($cells->getNode(3)->textContent ?? ''),
            'nombreReceptor' => trim($cells->getNode(4)->textContent ?? ''),
            'fechaEmision' => trim($cells->getNode(5)->textContent ?? ''),
            'fechaCertificacion' => trim($cells->getNode(6)->textContent ?? ''),
            'pacCertifico' => trim($cells->getNode(7)->textContent ?? ''),
            'total' => trim($cells->getNode(8)->textContent ?? ''),
            'efectoComprobante' => trim($cells->getNode(9)->textContent ?? ''),
            'estatusCancelacion' => trim($cells->getNode(10)->textContent ?? ''),
            'estadoComprobante' => trim($cells->getNode(11)->textContent ?? ''),
            'estatusProcesoCancelacion' => trim($cells->getNode(12)->textContent ?? ''),
            'fechaProcesoCancelacion' => trim($cells->getNode(13)->textContent ?? ''),
        ];
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
