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
        $tds = $row->filter('td[style="WORD-BREAK:BREAK-ALL;"]');
        
        return [
            'uuid' => trim($tds->getNode(0)->textContent),
            'rfcEmisor' => trim($tds->getNode(1)->textContent),
            'nombreEmisor' => trim($tds->getNode(2)->textContent),
            'rfcReceptor' => trim($tds->getNode(3)->textContent),
            'nombreReceptor' => trim($tds->getNode(4)->textContent),
            'fechaEmision' => trim($tds->getNode(5)->textContent),
            'fechaCertificacion' => trim($tds->getNode(6)->textContent),
            'pacCertifico' => trim($tds->getNode(7)->textContent),
            'total' => trim($tds->getNode(8)->textContent),
            'efectoComprobante' => trim($tds->getNode(9)->textContent),
            'estatusCancelacion' => trim($tds->getNode(10)->textContent),
            'estadoComprobante' => trim($tds->getNode(11)->textContent),
            'estatusProcesoCancelacion' => trim($tds->getNode(12)->textContent),
            'fechaProcesoCancelacion' => trim($tds->getNode(13)->textContent),
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
