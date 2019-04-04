<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use Symfony\Component\DomCrawler\Crawler;

class MetadataExtractor
{
    /** @var array[] */
    private $data = [];

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param string $html
     * @return int
     */
    public function extract(string $html): int
    {
        $crawler = new Crawler($html);

        $filteredElements = $crawler->filter('table#ctl00_MainContent_tblResult > tr')
            ->reduce(
                function (Crawler $node) {
                    return 'td' === $node->children()->first()->getNode(0)->tagName;
                }
            );

        $numberOfElements = $filteredElements->count();

        $filteredElements->each(
            function (Crawler $node): void {
                $tds = $node->filter('td[style="WORD-BREAK:BREAK-ALL;"]');

                $temp['uuid'] = trim($tds->getNode(0)->textContent);
                $temp['rfcEmisor'] = trim($tds->getNode(1)->textContent);
                $temp['nombreEmisor'] = trim($tds->getNode(2)->textContent);
                $temp['rfcReceptor'] = trim($tds->getNode(3)->textContent);
                $temp['nombreReceptor'] = trim($tds->getNode(4)->textContent);
                $temp['fechaEmision'] = trim($tds->getNode(5)->textContent);
                $temp['fechaCertificacion'] = trim($tds->getNode(6)->textContent);
                $temp['pacCertifico'] = trim($tds->getNode(7)->textContent);
                $temp['total'] = trim($tds->getNode(8)->textContent);
                $temp['efectoComprobante'] = trim($tds->getNode(9)->textContent);
                $temp['estatusCancelacion'] = trim($tds->getNode(10)->textContent);
                $temp['estadoComprobante'] = trim($tds->getNode(11)->textContent);
                $temp['estatusProcesoCancelacion'] = trim($tds->getNode(12)->textContent);
                $temp['fechaProcesoCancelacion'] = trim($tds->getNode(13)->textContent);
                $temp['fechaCancelacion'] = $temp['fechaProcesoCancelacion'];
                $temp['urlXml'] = null;

                $spansBtnDownload = $node->filter('span#BtnDescarga');
                $onClickAttribute = $spansBtnDownload->count() > 0 ? $spansBtnDownload->first()->attr('onclick') : null;

                if (! is_null($onClickAttribute)) {
                    $temp['urlXml'] = str_replace(
                        ["return AccionCfdi('", "','Recuperacion');"],
                        [URLS::SAT_URL_PORTAL_CFDI, ''],
                        $onClickAttribute
                    );
                }

                $this->data[$temp['uuid']] = $temp;
            }
        );

        return $numberOfElements;
    }
}
