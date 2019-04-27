<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\Query;
use PhpCfdi\CfdiSatScraper\QueryResolver;

class FakeQueryResolver extends QueryResolver
{
    private $fakeMoments = [];

    public $resolveCalls = [];

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
    }

    public function resolve(Query $query): MetadataList
    {
        $list = new MetadataList([]);
        foreach ($this->fakeMoments as $moment) {
            $date = $moment['date'];
            if ($date >= $query->getStartDate() && $date <= $query->getEndDate()) {
                $list = $list->merge($moment['list']);
            }
        }
        $this->resolveCalls[] = [
            'start' => $query->getStartDate()->format('Y-m-d H:i:s'),
            'end' => $query->getEndDate()->format('Y-m-d H:i:s'),
            'count' => $list->count(),
        ];
        return $list;
    }

    public function appendMoment(\DateTimeImmutable $date, MetadataList $list): void
    {
        $this->fakeMoments[] = [
            'date' => $date,
            'list' => $list,
        ];
    }
}
