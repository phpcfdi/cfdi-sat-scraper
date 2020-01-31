<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

/**
 * Class ParserFormatSAT.
 */
class ParserFormatSAT
{
    public $source;

    public $items;

    public $valids;

    /**
     * ParserFormatSAT constructor.
     *
     * @param $source
     */
    public function __construct($source)
    {
        $this->source = $source;
        $this->items = [];
        $this->valids = ['__EVENTTARGET', '__EVENTARGUMENT', '__LASTFOCUS', '__VIEWSTATE'];
    }

    /**
     * @return array
     */
    public function getFormValues()
    {
        $values = explode('|', $this->source);

        foreach (range(0, count($values) - 1) as $index) {
            $item = $values[$index];
            if (in_array($item, $this->valids)) {
                $name = $item;
                $index += 1;
                $item = $values[$index];
                $this->items[$name] = $item;
            }
        }

        return $this->items;
    }
}
