<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use DateTimeImmutable;
use Generator;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Filters\Options\ComplementsOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\RfcOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\UuidOption;

class Query
{
    /**
     * @var DateTimeImmutable
     */
    protected $startDate;

    /**
     * @var DateTimeImmutable
     */
    protected $endDate;

    /**
     * @var RfcOption
     */
    protected $rfc;

    /**
     * @var UuidOption
     */
    protected $uuid;

    /**
     * @var ComplementsOption
     */
    protected $complement;

    /**
     * @var StatesVoucherOption
     */
    protected $stateVoucher;

    /**
     * @var DownloadType
     */
    protected $downloadType;

    /**
     * Query constructor.
     * @param DateTimeImmutable $startDate
     * @param DateTimeImmutable $endDate
     */
    public function __construct(DateTimeImmutable $startDate, DateTimeImmutable $endDate)
    {
        if ($endDate < $startDate) {
            throw InvalidArgumentException::periodStartDateGreaterThanEndDate($startDate, $endDate);
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->downloadType = DownloadType::recibidos();
        $this->complement = ComplementsOption::todos();
        $this->stateVoucher = StatesVoucherOption::todos();
        $this->uuid = new UuidOption('');
        $this->rfc = new RfcOption('');
    }

    /**
     * @return DateTimeImmutable
     */
    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * @param DateTimeImmutable $startDate
     * @return Query
     */
    public function setStartDate(DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * @param DateTimeImmutable $endDate
     * @return Query
     */
    public function setEndDate(DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return RfcOption
     */
    public function getRfc(): RfcOption
    {
        return $this->rfc;
    }

    /**
     * @param UuidOption $uuid
     * @return Query
     */
    public function setUuid(UuidOption $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function hasUuid(): bool
    {
        return ('' !== $this->uuid->value());
    }

    /**
     * @return UuidOption
     */
    public function getUuid(): UuidOption
    {
        return $this->uuid;
    }

    /**
     * @param RfcOption $rfc
     *
     * @return Query
     */
    public function setRfc(RfcOption $rfc): self
    {
        $this->rfc = $rfc;

        return $this;
    }

    /**
     * @return ComplementsOption
     */
    public function getComplement(): ComplementsOption
    {
        return $this->complement;
    }

    /**
     * @param ComplementsOption $complement
     * @return Query
     */
    public function setComplement(ComplementsOption $complement): self
    {
        $this->complement = $complement;

        return $this;
    }

    /**
     * @return StatesVoucherOption
     */
    public function getStateVoucher(): StatesVoucherOption
    {
        return $this->stateVoucher;
    }

    /**
     * @param StatesVoucherOption $stateVoucher
     * @return Query
     */
    public function setStateVoucher(StatesVoucherOption $stateVoucher): self
    {
        $this->stateVoucher = $stateVoucher;

        return $this;
    }

    /**
     * @return DownloadType
     */
    public function getDownloadType(): DownloadType
    {
        return $this->downloadType;
    }

    /**
     * @param DownloadType $downloadType
     * @return Query
     */
    public function setDownloadType(DownloadType $downloadType): self
    {
        $this->downloadType = $downloadType;

        return $this;
    }

    /**
     * Generates a clone of this query splitted by day
     *
     * @return Generator|Query[]
     */
    public function splitByDays()
    {
        $endDate = $this->getEndDate();
        for ($date = $this->getStartDate(); $date <= $endDate; $date = $date->modify('midnight +1 day')) {
            $partial = clone $this;
            $partial->setStartDate($date);
            $partial->setEndDate(min($date->setTime(23, 59, 59), $endDate));
            yield $partial;
        }
    }
}
