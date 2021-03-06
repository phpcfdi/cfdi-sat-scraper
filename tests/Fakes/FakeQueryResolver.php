<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Fakes;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Inputs\InputsByFilters;
use PhpCfdi\CfdiSatScraper\Inputs\InputsInterface;
use PhpCfdi\CfdiSatScraper\Internal\QueryResolver;
use PhpCfdi\CfdiSatScraper\MetadataList;

final class FakeQueryResolver extends QueryResolver
{
    /** @var array<array{date: DateTimeImmutable, list: MetadataList}> */
    private $fakeMoments = [];

    /** @var array<array{start: string, end: string, count: int}> */
    public $resolveCalls = [];

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function resolve(InputsInterface $inputs): MetadataList
    {
        if ($inputs instanceof InputsByFilters) {
            return $this->resolveQueryByFilters($inputs);
        }
        return $this->resolveAllMoments();
    }

    public function resolveQueryByFilters(InputsByFilters $inputs): MetadataList
    {
        $query = $inputs->getQuery();
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

    public function resolveAllMoments(): MetadataList
    {
        $list = new MetadataList([]);
        foreach ($this->fakeMoments as $moment) {
            $list = $list->merge($moment['list']);
        }
        $this->resolveCalls[] = [
            'start' => 'n/a',
            'end' => 'n/a',
            'count' => $list->count(),
        ];
        return $list;
    }

    public function appendMoment(DateTimeImmutable $date, MetadataList $list): void
    {
        $this->fakeMoments[] = [
            'date' => $date,
            'list' => $list,
        ];
    }
}
