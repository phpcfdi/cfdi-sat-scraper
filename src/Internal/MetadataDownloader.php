<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use DateTimeImmutable;
use Generator;
use PhpCfdi\CfdiSatScraper\Contracts\MaximumRecordsHandler;
use PhpCfdi\CfdiSatScraper\Contracts\QueryInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Filters\Options\UuidOption;
use PhpCfdi\CfdiSatScraper\Inputs\InputsByFiltersIssued;
use PhpCfdi\CfdiSatScraper\Inputs\InputsByFiltersReceived;
use PhpCfdi\CfdiSatScraper\Inputs\InputsByUuid;
use PhpCfdi\CfdiSatScraper\Inputs\InputsInterface;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\QueryByUuid;

/**
 * Class MetadataDownloader contains the logic to manipulate queries to obtain metadata
 * Depends on QueryResolver to retrieve the contents
 * Has a copy of callable to raise when limit is reached
 *
 * @see QueryResolver
 * @internal
 */
class MetadataDownloader
{
    /** @var QueryResolver */
    private $queryResolver;

    /** @var MaximumRecordsHandler */
    private $maximumRecordsHandler;

    /** @internal */
    public function __construct(QueryResolver $queryResolver, MaximumRecordsHandler $maximumRecordsHandler)
    {
        $this->queryResolver = $queryResolver;
        $this->maximumRecordsHandler = $maximumRecordsHandler;
    }

    public function getQueryResolver(): QueryResolver
    {
        return $this->queryResolver;
    }

    public function getMaximumRecordsHandler(): MaximumRecordsHandler
    {
        return $this->maximumRecordsHandler;
    }

    /**
     * @param string[] $uuids
     * @param DownloadType $downloadType
     * @return MetadataList
     * @throws SatHttpGatewayException
     */
    public function downloadByUuids(array $uuids, DownloadType $downloadType): MetadataList
    {
        $uuids = array_keys(array_change_key_case(array_flip($uuids), CASE_LOWER));
        $result = new MetadataList([]);
        foreach ($uuids as $uuid) {
            $uuidResult = $this->downloadByUuid(new UuidOption($uuid), $downloadType);
            $result = $result->merge($uuidResult);
        }

        return $result;
    }

    /**
     * @param UuidOption $uuid
     * @param DownloadType $downloadType
     * @return MetadataList
     * @throws SatHttpGatewayException
     */
    public function downloadByUuid(UuidOption $uuid, DownloadType $downloadType): MetadataList
    {
        $query = new QueryByUuid($uuid, $downloadType);
        return $this->resolveQuery($query);
    }

    /**
     * @param QueryByFilters $query
     * @return MetadataList
     * @throws SatHttpGatewayException
     */
    public function downloadByDate(QueryByFilters $query): MetadataList
    {
        /** @var DateTimeImmutable $startDate set this type definition as setTime can return FALSE */
        $startDate = $query->getStartDate()->setTime(0, 0, 0);
        /** @var DateTimeImmutable $endDate set this type definition as setTime can return FALSE */
        $endDate = $query->getEndDate()->setTime(23, 59, 59);

        $query = clone $query;
        $query->setPeriod($startDate, $endDate);
        return $this->downloadByDateTime($query);
    }

    /**
     * @param QueryByFilters $query
     * @return MetadataList
     * @throws SatHttpGatewayException
     */
    public function downloadByDateTime(QueryByFilters $query): MetadataList
    {
        $result = new MetadataList([]);
        foreach ($this->splitQueryByFiltersByDays($query) as $current) {
            $result = $result->merge($this->downloadQuery($current));
        }
        return $result;
    }

    /**
     * @param QueryByFilters $query
     * @return MetadataList
     * @throws SatHttpGatewayException
     */
    public function downloadQuery(QueryByFilters $query): MetadataList
    {
        $finalList = new MetadataList([]);
        $day = $query->getStartDate()->modify('midnight');
        $lowerBound = intval($query->getStartDate()->format('U')) - intval($day->format('U'));
        $upperBound = intval($query->getEndDate()->format('U')) - intval($day->format('U'));
        $secondInitial = $lowerBound;
        $secondEnd = $upperBound;

        while (true) {
            $currentQuery = $this->newQueryWithSeconds($query, $secondInitial, $secondEnd);
            $list = $this->resolveQuery($currentQuery);
            $result = $list->count();

            if ($result >= 500 && $secondEnd === $secondInitial) {
                $this->raiseOnLimit($this->buildDateWithDayAndSeconds($day, $secondInitial));
            }

            if ($result >= 500 && $secondEnd > $secondInitial) {
                $secondEnd = (int)floor($secondInitial + (($secondEnd - $secondInitial) / 2));
                continue;
            }

            $finalList = $finalList->merge($list);
            if ($secondEnd >= $upperBound) {
                break;
            }

            $secondInitial = $secondEnd + 1;
            $secondEnd = $upperBound;
        }

        return $finalList;
    }

    public function newQueryWithSeconds(QueryByFilters $query, int $startSec, int $endSec): QueryByFilters
    {
        return (clone $query)->setPeriod(
            $this->buildDateWithDayAndSeconds($query->getStartDate(), $startSec),
            $this->buildDateWithDayAndSeconds($query->getEndDate(), $endSec),
        );
    }

    /**
     * @param QueryInterface $query
     * @return MetadataList
     * @throws SatHttpGatewayException
     * @see QueryResolver
     */
    public function resolveQuery(QueryInterface $query): MetadataList
    {
        $inputs = $this->createInputsFromQuery($query);
        return $this->getQueryResolver()->resolve($inputs);
    }

    public function buildDateWithDayAndSeconds(DateTimeImmutable $day, int $seconds): DateTimeImmutable
    {
        return $day->modify(sprintf('midnight + %d seconds', $seconds));
    }

    public function raiseOnLimit(DateTimeImmutable $date): void
    {
        $this->maximumRecordsHandler->handle($date);
    }

    public function createInputsFromQuery(QueryInterface $query): InputsInterface
    {
        if ($query instanceof QueryByFilters) {
            if ($query->getDownloadType()->isEmitidos()) {
                return new InputsByFiltersIssued($query);
            }
            return new InputsByFiltersReceived($query);
        }
        if ($query instanceof QueryByUuid) {
            return new InputsByUuid($query);
        }
        throw LogicException::generic(sprintf('Unable to create input filters from query type %s', get_class($query)));
    }

    /**
     * Generates a clone of this query split by day
     *
     * @param QueryByFilters $query
     * @return Generator<QueryByFilters>
     */
    public function splitQueryByFiltersByDays(QueryByFilters $query): Generator
    {
        $endDate = $query->getEndDate();
        for ($date = $query->getStartDate(); $date <= $endDate; $date = $date->modify('midnight +1 day')) {
            $partial = clone $query;
            /** @var DateTimeImmutable $dateOnLastSecond set this type definition as setTime can return FALSE */
            $dateOnLastSecond = $date->setTime(23, 59, 59);
            $partial->setPeriod($date, min($dateOnLastSecond, $endDate));
            yield $partial;
        }
    }
}
