<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Contracts\Filters;
use PhpCfdi\CfdiSatScraper\Filters\FiltersIssued;
use PhpCfdi\CfdiSatScraper\Filters\FiltersReceived;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Internal\Headers;
use PhpCfdi\CfdiSatScraper\Internal\HtmlForm;
use PhpCfdi\CfdiSatScraper\Internal\ParserFormatSAT;

class QueryResolver
{
    /** @var Client */
    private $client;

    /** @var CookieJar */
    private $cookie;

    public function __construct(Client $client, CookieJar $cookie)
    {
        $this->client = $client;
        $this->cookie = $cookie;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getCookie(): CookieJar
    {
        return $this->cookie;
    }

    public function resolve(Query $query): MetadataList
    {
        // define url by download type
        $url = $this->urlFromDownloadType($query->getDownloadType());

        // extract main inputs
        $html = $this->consumeFormPage($url);
        // hack for bad encoding
        $html = str_replace('charset=utf-16', 'charset=utf-8', $html);

        // get first set of inputs from the search page
        $htmlFormInputExtractor = new HtmlForm($html, 'form', ['/^seleccionador$/']);
        $baseInputs = $htmlFormInputExtractor->getFormValues();

        // create filters from query
        $filters = $this->filtersFromQuery($query);

        // consume search using initial inputs and inputs to select the filter (UUID or Search)
        $post = array_merge($baseInputs, $filters->getInitialFilters());
        $html = $this->consumeSearch($url, $post); // this html is used to update __VARIABLES
        $lastViewStateValues = (new ParserFormatSAT())->getFormValues($html);

        // consume search using search filters
        $post = array_merge($baseInputs, $filters->getRequestFilters(), $lastViewStateValues);
        $htmlSearch = $this->consumeSearch($url, $post);

        // extract data from resolved search
        return (new MetadataExtractor())->extract($htmlSearch);
    }

    protected function consumeFormPage(string $url): string
    {
        $response = $this->getClient()->get($url, [
            'future' => true,
            'cookies' => $this->getCookie(),
            'verify' => false,
        ]);

        return $response->getBody()->getContents();
    }

    protected function consumeSearch(string $url, array $formParams): string
    {
        $response = $this->getClient()->post($url, [
            'form_params' => $formParams,
            'headers' => Headers::postAjax(URLS::SAT_HOST_PORTAL_CFDI, $url),
            'future' => true,
            'verify' => false,
            'cookies' => $this->getCookie(),
        ]);
        return $response->getBody()->getContents();
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
}
