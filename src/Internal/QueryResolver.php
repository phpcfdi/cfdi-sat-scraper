<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\Inputs\InputsInterface;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;

/**
 * This class is a method extraction for MetadataDownloader::resolveQuery
 * The entry point is the resolve method.
 *
 * @see QueryResolver::resolve()
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

    /**
     * @param InputsInterface $inputs
     * @return MetadataList
     * @throws SatHttpGatewayException
     */
    public function resolve(InputsInterface $inputs): MetadataList
    {
        $url = $inputs->getUrl();
        $ajaxFilters = $inputs->getAjaxInputs();

        // access to download type page, it returns the hole set of inputs
        $baseInputs = $this->inputFieldsFromInitialPage($url);

        // select query type (uuid or filters), it returns only a subset of inputs
        $post = array_merge($baseInputs, $ajaxFilters);
        $lastViewStates = $this->inputFieldsFromSelectDownloadType($url, $post);

        // execute search
        $post = array_merge($baseInputs, $ajaxFilters, $lastViewStates, $inputs->getQueryAsInputs());
        $htmlWithMetadata = $this->htmlFromExecuteQuery($url, $post);

        // extract metadata from search results
        return (new MetadataExtractor())->extract($htmlWithMetadata);
    }

    /**
     * @param string $url
     * @return array<string, string>
     * @throws SatHttpGatewayException
     */
    protected function inputFieldsFromInitialPage(string $url): array
    {
        $completePage = $this->getSatHttpGateway()->getPortalPage($url);
        $completePage = str_replace('charset=utf-16', 'charset=utf-8', $completePage); // quick and dirty hack
        $htmlFormInputExtractor = new HtmlForm($completePage, 'form', ['/^ctl00\$MainContent\$Btn.+/', '/^seleccionador$/']);
        return $htmlFormInputExtractor->getFormValues();
    }

    /**
     * @param string $url
     * @param array<string, string> $post
     * @return array<string, string>
     * @throws SatHttpGatewayException
     */
    protected function inputFieldsFromSelectDownloadType(string $url, array $post): array
    {
        $html = $this->getSatHttpGateway()->postAjaxSearch($url, $post); // this html is used to update __VARIABLES
        return (new ParserFormatSAT())->getFormValues($html);
    }

    /**
     * @param string $url
     * @param array<string, string> $post
     * @return string
     * @throws SatHttpGatewayException
     */
    protected function htmlFromExecuteQuery(string $url, array $post): string
    {
        return $this->getSatHttpGateway()->postAjaxSearch($url, $post);
    }

    public function getSatHttpGateway(): SatHttpGateway
    {
        return $this->satHttpGateway;
    }
}
