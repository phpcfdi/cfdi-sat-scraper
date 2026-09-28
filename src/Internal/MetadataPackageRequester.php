<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\Exceptions\MetadataPackageException;
use PhpCfdi\CfdiSatScraper\Exceptions\PackageIsNotAvailableException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\Inputs\InputsByFiltersIssued;
use PhpCfdi\CfdiSatScraper\Inputs\InputsByFiltersReceived;
use PhpCfdi\CfdiSatScraper\Inputs\InputsInterface;
use PhpCfdi\CfdiSatScraper\MetadataPackage\AvailablePackage;
use PhpCfdi\CfdiSatScraper\MetadataPackage\AvailablePackageList;
use PhpCfdi\CfdiSatScraper\MetadataPackage\QueryResult;
use PhpCfdi\CfdiSatScraper\MetadataPackage\QueryResults;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\URLS;
use RuntimeException;
use Throwable;

/**
 * This class contains the logic to request and download metadata packages (ZIP files)
 * using the massive download flow of the SAT portal.
 *
 * @internal
 */
class MetadataPackageRequester
{
    public function __construct(private readonly SatHttpGateway $gateway)
    {
    }

    public function getGateway(): SatHttpGateway
    {
        return $this->gateway;
    }

    public function createInputsFromQuery(QueryByFilters $query): InputsInterface
    {
        return match (true) {
            $query->getDownloadType()->isEmitidos() => new InputsByFiltersIssued($query),
            $query->getDownloadType()->isRecibidos() => new InputsByFiltersReceived($query),
            default => throw LogicException::generic('Cannot convert QueryByFilters to InputsInterface'),
        };
    }

    /**
     * Request the generation of the metadata package for the given query.
     *
     * When the download type is "recibidos" and the period spans more than one day,
     * the query is split by day or whole month (one package per month)
     * since the portal does not accept multi-days ranges.
     *
     * @throws MetadataPackageException when the search has no results or the portal rejects the request
     */
    public function requestPackages(QueryByFilters $query): QueryResults
    {
        $results = [];
        $splitter = new MetadataPackageQuerySplitter($query);
        foreach ($splitter->split() as $current) {
            try {
                $packageUuid = $this->requestPackage($current);
                $result = new QueryResult($packageUuid, $current->getStartDate(), $current->getEndDate());
            } catch (MetadataPackageException $exception) {
                $result = new QueryResult('', $current->getStartDate(), $current->getEndDate(), $exception);
            }
            $results[] = $result;
        }

        return new QueryResults(...$results);
    }

    /**
     * Request the generation of the metadata package for a query and return the package uuid
     *
     * @return non-empty-string
     * @throws SatHttpGatewayException
     * @throws MetadataPackageException if search has no result or did not return parameters
     * @throws MetadataPackageException if request is rejected with an error
     * @throws MetadataPackageException if did not return an UUID
     */
    public function requestPackage(QueryByFilters $query): string
    {
        $inputs = $this->createInputsFromQuery($query);

        $queryResolver = new QueryResolver($this->gateway);
        $html = $queryResolver->executeSearch($inputs);

        // use MetadataExtractor to know that the query has rows
        $extractor = new MetadataExtractor();
        if (null === $extractor->extractRows($html)) {
            throw MetadataPackageException::searchHasNoResults();
        }

        $parameters = $this->extractMetadataParameters($html);
        if ('' === $parameters) {
            throw MetadataPackageException::searchHasNoResults();
        }

        return $this->requestFolio($inputs->getUrl(), $parameters);
    }

    /**
     * List the packages that are ready to be downloaded (visible on the portal, last 3 days)
     */
    public function listAvailablePackages(): AvailablePackageList
    {
        $packagesPage = $this->gateway->getPortalPage(URLS::PORTAL_CFDI_DESCARGA_MASIVA);
        return $this->extractAvailablePackages($packagesPage);
    }

    /**
     * Download the ZIP file of a package already available (see listAvailablePackages)
     *
     * @throws PackageIsNotAvailableException when the package is not available,
     * the portal returns a html page instead of the zip, or the file cannot be written.
     * @throws MetadataPackageException when the file cannot be written
     */
    public function downloadPackage(string $uuid, string $destinationPath): void
    {
        $html = $this->gateway->getPortalPage(URLS::PORTAL_CFDI_DESCARGA_MASIVA);
        $available = $this->extractAvailablePackages($html);

        $this->downloadPackageFromAvailable($available, $uuid, $destinationPath);
    }

    public function downloadPackageFromAvailable(AvailablePackageList $available, string $uuid, string $destinationPath): void
    {
        if (! $available->has($uuid)) {
            throw new PackageIsNotAvailableException($uuid);
        }

        $package = $available->get($uuid);
        $fields = $available->getBaseFields();
        $fields['__EVENTTARGET'] = 'ctl00$MainContent$setLinkButtonDescarga';
        $fields['__EVENTARGUMENT'] = '';
        $fields['ctl00$MainContent$hfFolioDescargaActual'] = $package->getUuid();
        $fields['ctl00$MainContent$hfUrlDescargaActual'] = $package->getDownloadUrl();

        $contents = $this->gateway->postDownloadPackage($fields);

        if (! $this->isZipContents($contents)) {
            throw MetadataPackageException::invalidPackageContents($uuid, $contents);
        }

        $this->writePackageFile($destinationPath, $contents);
    }

    /**
     * Check that the contents look like a ZIP file (they start with the PK signature).
     * The portal returns a html page when the postback is not valid, so this check
     * prevents storing a html document with a zip extension.
     */
    private function isZipContents(string $contents): bool
    {
        return str_starts_with($contents, "PK\x03\x04")
            || str_starts_with($contents, "PK\x05\x06")  // empty zip
            || str_starts_with($contents, "PK\x07\x08"); // spanned zip
    }

    /**
     * Extract the value of the hidden field "hfParametrosMetadata" from the search results page.
     * Returns an empty string when the field is not present (the search has no results).
     */
    public function extractMetadataParameters(string $html): string
    {
        if (! preg_match('/id="hfParametrosMetadata"[^>]*value="([^"]*)"/', $html, $matches)) {
            return '';
        }

        return html_entity_decode($matches[1]);
    }

    /**
     * Extract the folio (UUID) and download url of the packages listed on the given
     * ConsultaDescargaMasiva page contents.
     */
    public function extractAvailablePackages(string $html): AvailablePackageList
    {
        preg_match_all(
            "/AccionRetencion\('([0-9A-Fa-f-]{36})','([^']+)','Recuperacion'\)/",
            $html,
            $matches,
            PREG_SET_ORDER,
        );

        $packages = [];
        foreach ($matches as $match) {
            $packages[] = new AvailablePackage($match[1], html_entity_decode($match[2]));
        }

        $baseFields = $this->extractHiddenFields($html);
        return (new AvailablePackageList(...$packages))->withBaseFields($baseFields);
    }

    /**
     * Call the PageMethod "<url>/DescargaMetadatos" to request the package generation
     * and extract the folio (UUID) from the portal response.
     *
     * @return non-empty-string
     * @throws MetadataPackageException if request is rejected with an error
     * @throws MetadataPackageException if did not return an UUID
     */
    private function requestFolio(string $url, string $parameters): string
    {
        $endpoint = $url . '/DescargaMetadatos';
        $encoded = rawurlencode(base64_encode($parameters));

        // the portal expects literal single quotes (the format used by its own JavaScript)
        $response = $this->gateway->postJson($endpoint, sprintf("{'Parametros':'%s'}", $encoded));

        $data = json_decode($response, true);
        $message = is_array($data) && isset($data['d']) && is_scalar($data['d']) ? (string) $data['d'] : '';

        if (str_starts_with($message, 'Error:')) {
            throw MetadataPackageException::requestRejected($message);
        }

        preg_match(
            '/folio de descarga:\s*([0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12})/',
            $message,
            $matches,
        );
        $folio = $matches[1] ?? '';
        if ('' === $folio) {
            throw MetadataPackageException::unableToObtainFolio($message);
        }

        return $folio;
    }

    /**
     * Extract all the hidden inputs of the page as name => value.
     *
     * The portal renders the attributes of the inputs in different orders and may
     * include extra attributes (id, etc.) between them, so a strict attribute-order
     * regular expression would miss required fields like __VIEWSTATE or __CSRFTOKEN.
     *
     * @return array<string, string>
     */
    private function extractHiddenFields(string $html): array
    {
        preg_match_all('/<input\b[^>]*>/i', $html, $inputs);

        $fields = [];
        foreach ($inputs[0] as $input) {
            if (! preg_match('/\btype\s*=\s*["\']?hidden["\']?/i', $input)) {
                continue;
            }
            $name = $this->extractAttribute($input, 'name');
            if ('' === $name) {
                continue;
            }
            $fields[$name] = $this->extractAttribute($input, 'value');
        }

        return $fields;
    }

    /**
     * Extract the html-decoded value of an attribute from an element, whatever
     * its position and whether it is single-quoted, double-quoted or unquoted.
     */
    private function extractAttribute(string $element, string $attribute): string
    {
        $pattern = sprintf('/\b%s\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', preg_quote($attribute, '/'));
        if (! preg_match($pattern, $element, $matches)) {
            return '';
        }
        // the value is in the first participating capturing group (double, single or unquoted)
        foreach ([1, 2, 3] as $group) {
            if (isset($matches[$group]) && '' !== $matches[$group]) {
                return html_entity_decode($matches[$group]);
            }
        }
        return '';
    }

    /**
     * @throws MetadataPackageException if unable to write package
     */
    private function writePackageFile(string $destinationPath, string $packageContents): void
    {
        try {
            if (false === file_put_contents($destinationPath, $packageContents)) {
                throw new RuntimeException('Call to file_put_contents() failed');
            }
        } catch (Throwable $exception) {
            throw MetadataPackageException::unableToWritePackage($destinationPath, previous: $exception);
        }
    }
}
