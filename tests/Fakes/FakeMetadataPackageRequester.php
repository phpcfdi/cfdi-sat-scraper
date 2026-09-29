<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Fakes;

use OutOfBoundsException;
use PhpCfdi\CfdiSatScraper\Internal\MetadataPackageRequester;
use PhpCfdi\CfdiSatScraper\MetadataPackage\QueryResult;
use PhpCfdi\CfdiSatScraper\QueryByFilters;

final class FakeMetadataPackageRequester extends MetadataPackageRequester
{
    /** @var list<array{query: QueryByFilters, result: QueryResult}> */
    private array $captured = [];

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
        // Do not call parent constructor
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function requestPackage(QueryByFilters $query): string
    {
        $fakeUuid = uniqid('unreal-uuid-', true);
        $result = new QueryResult($fakeUuid, $query->getStartDate(), $query->getEndDate());
        $this->captured[] = [
            'query' => $query,
            'result' => $result,
        ];

        return $fakeUuid;
    }

    /** @return list<array{query: QueryByFilters, result: QueryResult}> */
    public function getCaptured(): array
    {
        return $this->captured;
    }

    /** @return array{query: QueryByFilters, result: QueryResult} */
    public function getCapture(int $index): array
    {
        return $this->captured[$index] ?? throw new OutOfBoundsException('No captured metadata found.');
    }
}
