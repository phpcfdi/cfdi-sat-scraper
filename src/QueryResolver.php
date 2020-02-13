<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Contracts\Filters;
use PhpCfdi\CfdiSatScraper\Filters\FiltersIssued;
use PhpCfdi\CfdiSatScraper\Filters\FiltersReceived;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Internal\HtmlForm;
use PhpCfdi\CfdiSatScraper\Internal\ParserFormatSAT;

class QueryResolver
{
    /** @var SatHttpGateway */
    private $satHttpGateway;

    public function __construct(SatHttpGateway $satHttpGateway)
    {
        $this->satHttpGateway = $satHttpGateway;
    }

    public function resolve(Query $query): MetadataList
    {
        $filters = $this->filtersFromQuery($query); // create filters from query
        $url = $this->urlFromDownloadType($query->getDownloadType()); // define url by download type

        $baseInputs = $this->resolveOpenCompletePage($url);
        $lastViewStates = $this->resolveSelectDownloadType($url, $baseInputs, $filters);
        $htmlWithMetadata = $this->resolveObtainList($url, array_merge($baseInputs, $lastViewStates), $filters);

        return (new MetadataExtractor())->extract($htmlWithMetadata);
    }

    protected function resolveOpenCompletePage(string $url): array
    {
        $completePage = $this->consumeFormPage($url);
        $completePage = str_replace('charset=utf-16', 'charset=utf-8', $completePage); // quick and dirty hack
        $htmlFormInputExtractor = new HtmlForm($completePage, 'form', ['/^ctl00\$MainContent\$Btn.+/', '/^seleccionador$/']);
        $baseInputs = $htmlFormInputExtractor->getFormValues();
        return $baseInputs;
    }

    protected function resolveSelectDownloadType(string $url, array $baseInputs, Filters $filters): array
    {
        $post = array_merge($baseInputs, $filters->getInitialFilters());
        $html = $this->consumeSearch($url, $post); // this html is used to update __VARIABLES
        $lastViewStateValues = (new ParserFormatSAT())->getFormValues($html);
        return $lastViewStateValues;
    }

    protected function resolveObtainList(string $url, array $baseInputs, Filters $filters): string
    {
        // consume search using search filters
        $post = array_merge($baseInputs, $filters->getRequestFilters());
        $htmlWithMetadataContent = $this->consumeSearch($url, $post);
        return $htmlWithMetadataContent;
    }

    protected function consumeFormPage(string $url): string
    {
        return $this->satHttpGateway->getPortalPage($url);
    }

    protected function consumeSearch(string $url, array $formParams): string
    {
        return $this->satHttpGateway->postAjaxSearch($url, $formParams);
    }

    public function urlFromDownloadType(DownloadTypesOption $downloadType): string
    {
        if ($downloadType->isEmitidos()) {
            return URLS::SAT_URL_PORTAL_CFDI_CONSULTA_EMISOR;
        }
        return URLS::SAT_URL_PORTAL_CFDI_CONSULTA_RECEPTOR;
    }

    public function filtersFromQuery(Query $query): Filters
    {
        if ($query->getDownloadType()->isEmitidos()) {
            return new FiltersIssued($query);
        }
        return new FiltersReceived($query);
    }

    public function getSatHttpGateway(): SatHttpGateway
    {
        return $this->satHttpGateway;
    }
}
