<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use DOMElement;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Utility class to extract data from an HTML form.
 *
 * @internal
 */
class HtmlForm
{
    /** @var string */
    protected $parentElement;

    /** @var Crawler */
    protected $crawler;

    /** @var string[] */
    protected $elementNameExcludePatters;

    /**
     * HtmlForm constructor.
     *
     * @param string $htmlSource
     * @param string $parentElement
     * @param string[] $elementNameExcludePatters
     */
    public function __construct(string $htmlSource, string $parentElement, array $elementNameExcludePatters = [])
    {
        $this->setHtmlSource($htmlSource);
        $this->setParentElement($parentElement);
        $this->setElementNameExcludePatterns(...$elementNameExcludePatters);
    }

    public function setHtmlSource(string $htmlSource): void
    {
        $this->crawler = new Crawler($htmlSource);
    }

    public function setParentElement(string $parentElement): void
    {
        $this->parentElement = $parentElement;
    }

    public function setElementNameExcludePatterns(string ...$elementNameExcludePatters): void
    {
        $this->elementNameExcludePatters = $elementNameExcludePatters;
    }

    /**
     * Return all inputs (without submit, reset and button) and selects,
     * following the element name exclusion patterns
     *
     * @return array<string, string>
     */
    public function getFormValues(): array
    {
        return array_merge($this->readInputValues(), $this->readSelectValues());
    }

    /**
     * Retrieve an array with key as input element name and value as value
     * It excludes the inputs which name match with an exclusion pattern
     * This excludes all inputs with types submit, reset and button
     * In the case of input type radio it only includes it when is checked
     *
     * @return array<string, string>
     */
    public function readInputValues(): array
    {
        return $this->readFormElementsValues('input', ['submit', 'reset', 'button']);
    }

    /**
     * Retrieve an array with key as select element name and value as first option selected
     *
     * @return array<string, string>
     */
    public function readSelectValues(): array
    {
        $data = [];
        /** @var DOMElement[] $elements */
        $elements = $this->filterCrawlerElements("$this->parentElement select");
        foreach ($elements as $element) {
            $name = $element->getAttribute('name');
            if ($this->elementNameIsExcluded($name)) {
                continue;
            }

            $value = '';
            /** @var DOMElement $option */
            foreach ($element->getElementsByTagName('option') as $option) {
                if ($option->getAttribute('selected')) {
                    $value = $option->getAttribute('value');
                    break;
                }
            }

            $data[$name] = $value;
        }

        return $data;
    }

    /**
     * This method is compatible with elements that have a name and value
     * It excludes the selects which name match with an exclusion pattern
     * If type is defined is excluded if was set as an excluded type
     * If type is radio is included only if checked attribute is true-ish
     *
     * @param string $element
     * @param string[] $excludeTypes
     *
     * @return array<string, string>
     */
    public function readFormElementsValues(string $element, array $excludeTypes = []): array
    {
        $excludeTypes = array_map('strtolower', $excludeTypes);
        $data = [];

        /** @var DOMElement[] $elements */
        $elements = $this->filterCrawlerElements("$this->parentElement $element");
        foreach ($elements as $element) {
            $name = $element->getAttribute('name');
            if ($this->elementNameIsExcluded($name)) {
                continue;
            }

            $type = strtolower($element->getAttribute('type'));
            if (in_array($type, $excludeTypes, true)) {
                continue;
            }
            if (('radio' === $type || 'checkbox' === $type) && ! $element->getAttribute('checked')) {
                continue;
            }

            $data[$name] = $element->getAttribute('value');
        }

        return $data;
    }

    public function elementNameIsExcluded(string $name): bool
    {
        foreach ($this->elementNameExcludePatters as $excludePattern) {
            if (1 === preg_match($excludePattern, $name)) {
                return true;
            }
        }
        return false;
    }

    /**
     * This method is made to ignore RuntimeException if the CssSelector Component is not available.
     *
     * @param string $filter
     * @return Crawler|DOMElement[]
     */
    private function filterCrawlerElements(string $filter)
    {
        try {
            $elements = $this->crawler->filter($filter);
        } catch (RuntimeException $exception) {
            $elements = [];
        }
        /** @var Crawler|DOMElement[] $elements */
        return $elements;
    }
}
