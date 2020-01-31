<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

/**
 * Class ParserFormatSAT.
 */
class ParserFormatSAT
{
    public $source;

    /** @var string[] array of field names to filter */
    private $valids = ['__EVENTTARGET', '__EVENTARGUMENT', '__LASTFOCUS', '__VIEWSTATE'];

    /**
     * ParserFormatSAT constructor.
     *
     * @param $source
     */
    public function __construct($source)
    {
        $this->source = $source;
    }

    /**
     * Parse and retrieve only the preconfigured valid keys
     *
     * @return array<string, string>
     */
    public function getFormValues(): array
    {
        // format is: |value-length|field-type|field-name|value

        $values = explode('|', ltrim($this->source, '|'));
        $length = count($values);

        $items = [];
        for ($index = 0; $index < $length; $index = $index + 4) {
            $fieldName = $values[$index + 2] ?? '';
            if (in_array($fieldName, $this->valids, true)) {
                $items[$fieldName] = $values[$index + 3] ?? '';
            }
        }

        return $items;
    }
}
