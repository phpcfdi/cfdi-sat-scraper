<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use PhpCfdi\CfdiSatScraper\Contracts\Filters;
use PhpCfdi\CfdiSatScraper\Filters\FiltersIssued;
use PhpCfdi\CfdiSatScraper\Filters\FiltersReceived;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\Query;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;

/**
 * @internal
 */
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
        $filters = $this->createFiltersFromQuery($query); // create filters from query
        $url = $query->getDownloadType()->url(); // define url by download type

        $baseInputs = $this->inputFieldsFromInitialPage($url);
        $lastViewStates = $this->inputFieldsFromSelectDownloadType($url, $baseInputs, $filters);
        $htmlWithMetadata = $this->htmlFromExecuteQuery($url, array_merge($baseInputs, $lastViewStates), $filters);

        return (new MetadataExtractor())->extract($htmlWithMetadata);
    }

    protected function inputFieldsFromInitialPage(string $url): array
    {
        $completePage = $this->satHttpGateway->getPortalPage($url);
        $completePage = str_replace('charset=utf-16', 'charset=utf-8', $completePage); // quick and dirty hack
        $htmlFormInputExtractor = new HtmlForm($completePage, 'form', ['/^ctl00\$MainContent\$Btn.+/', '/^seleccionador$/']);
        $baseInputs = $htmlFormInputExtractor->getFormValues();
        return $baseInputs;
    }

    protected function inputFieldsFromSelectDownloadType(string $url, array $baseInputs, Filters $filters): array
    {
        $post = array_merge($baseInputs, $filters->getInitialFilters());
        $html = $this->satHttpGateway->postAjaxSearch($url, $post); // this html is used to update __VARIABLES
        $lastViewStateValues = (new ParserFormatSAT())->getFormValues($html);
        return $lastViewStateValues;
    }

    protected function htmlFromExecuteQuery(string $url, array $baseInputs, Filters $filters): string
    {
        // consume search using search filters
        $post = array_merge($baseInputs, $filters->getRequestFilters());
        $htmlWithMetadataContent = $this->satHttpGateway->postAjaxSearch($url, $post);
        return $htmlWithMetadataContent;
    }

    public function createFiltersFromQuery(Query $query): Filters
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
