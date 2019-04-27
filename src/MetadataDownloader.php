<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;

class MetadataDownloader
{
    /** @var QueryResolver */
    private $queryResolver;

    /** @var callable|null */
    private $onFiveHundred = null;

    public function __construct(QueryResolver $queryResolver, ?callable $onFiveHundred)
    {
        $this->queryResolver = $queryResolver;
        $this->onFiveHundred = $onFiveHundred;
    }

    public function getQueryResolver(): QueryResolver
    {
        return $this->queryResolver;
    }

    public function getOnFiveHundred(): ?callable
    {
        return $this->onFiveHundred;
    }

    public function downloadByUuids(array $uuids, DownloadTypesOption $downloadType): MetadataList
    {
        $query = new Query(new \DateTimeImmutable(), new \DateTimeImmutable());
        $query->setDownloadType($downloadType);

        $result = new MetadataList([]);
        foreach ($uuids as $uuid) {
            $query->setUuid([$uuid]);
            $uuidResult = $this->resolveQuery($query);
            $result = $result->merge($uuidResult);
        }

        return $result;
    }

    public function downloadByDate(Query $query): MetadataList
    {
        $query = clone $query;
        $query->setStartDate($query->getStartDate()->setTime(0, 0, 0));
        $query->setEndDate($query->getEndDate()->setTime(23, 59, 59));
        return $this->downloadByDateTime($query);
    }

    public function downloadByDateTime(Query $query): MetadataList
    {
        $result = new MetadataList([]);
        foreach ($query->splitByDays() as $current) {
            $result = $result->merge($this->downloadQuery($current));
        }
        return $result;
    }

    public function downloadQuery(Query $query): MetadataList
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

    public function newQueryWithSeconds(Query $query, int $startSec, int $endSec): Query
    {
        $query = clone $query;
        $query->setStartDate($this->buildDateWithDayAndSeconds($query->getStartDate(), $startSec));
        $query->setEndDate($this->buildDateWithDayAndSeconds($query->getEndDate(), $endSec));
        return $query;
    }

    public function resolveQuery(Query $query): MetadataList
    {
        return $this->getQueryResolver()->resolve($query);
    }

    public function buildDateWithDayAndSeconds(\DateTimeImmutable $day, int $seconds): \DateTimeImmutable
    {
        return $day->modify(sprintf('midnight + %d seconds', $seconds));
    }

    public function raiseOnLimit(\DateTimeImmutable $date): void
    {
        if (null === $this->onFiveHundred) {
            return;
        }
        call_user_func($this->onFiveHundred, $date);
    }
}
