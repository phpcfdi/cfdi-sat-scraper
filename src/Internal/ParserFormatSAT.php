<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

/**
 * This class is a helper to parse that incredibly weird responses from SAT that contains internal
 * information in a pipe delimited list.
 *
 * format: |value-length|field-type|field-name|value
 * example: |3|hiddenField|__FOO|foo|0|hiddenField|__EMPTY||16|hiddenField|__BAR|0123456789abcdef|
 * contents: __FOO: foo, __EMPTY: , __BAR: 0123456789abcdef
 *
 * @internal
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
        $values = explode('|', $source);
        $items = [];

        foreach (self::FILTER_KEYS as $key) {
            if (false !== $index = array_search($key, $values, true)) {
                $items[$key] = $values[$index + 1] ?? '';
            }
        }

        return $items;
    }
}
