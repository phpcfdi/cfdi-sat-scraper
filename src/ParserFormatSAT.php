<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

/**
 * Class ParserFormatSAT.
 */
class ParserFormatSAT
{
    /** @var string[] array of field names to filter */
    private const FILTER_KEYS = ['__EVENTTARGET', '__EVENTARGUMENT', '__LASTFOCUS', '__VIEWSTATE'];

    /**
     * Parse source and retrieve only the preconfigured valid keys
     *
     * @param string $source
     * @return array<string, string>
     */
    public function getFormValues(string $source): array
    {
        // format is: |value-length|field-type|field-name|value

        $values = explode('|', ltrim($source, '|'));
        $length = count($values);

        $items = [];
        for ($index = 0; $index < $length; $index = $index + 4) {
            $fieldName = $values[$index + 2] ?? '';
            if (in_array($fieldName, self::FILTER_KEYS, true)) {
                $items[$fieldName] = $values[$index + 3] ?? '';
            }
        }

        return $items;
    }
}
