<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use Symfony\Component\DomCrawler\Crawler;

class HtmlForm
{
    /**
     * @var string
     */
    protected $parentElement;

    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * HtmlForm constructor.
     * @param string $htmlSource
     * @param string $parentElement
     */
    public function __construct(string $htmlSource, string $parentElement)
    {
        $this->parentElement = $parentElement;
        $this->crawler = new Crawler($htmlSource);
    }

    /**
     * @return array
     */
    public function getFormValues(): array
    {
        $inputValues = $this->readInputValues();
        $selectValues = $this->readSelectValues();

        $values = array_merge($inputValues, $selectValues);

        return $values;
    }

    /**
     * @return array
     */
    public function readInputValues(): array
    {
        return $this->readAndGetValues('input');
    }

    /**
     * @return array
     */
    public function readSelectValues(): array
    {
        return $this->readAndGetValues('select');
    }

    /**
     * @param $element
     *
     * @return array
     */
    public function readAndGetValues(string $element): array
    {
        $data = [];
        $elements = $this->crawler->filter("{$this->parentElement} > $element");

        foreach ($elements as $element) {
            $data[$element->getAttribute('name')] = $element->getAttribute('value');
        }

        return $data;
    }
}
