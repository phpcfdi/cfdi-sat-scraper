<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Contracts\QueryInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Filters\Options\ComplementsOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\RfcOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\Internal\DownloadTypePropertyTrait;

/**
 * This class stores all the data to perform a search by filters
 */
class QueryByFilters implements QueryInterface
{
    use DownloadTypePropertyTrait;

    /** @var DateTimeImmutable */
    private $startDate;

    /** @var DateTimeImmutable */
    private $endDate;

    /** @var RfcOption */
    private $rfc;

    /** @var ComplementsOption */
    private $complement;

    /** @var StatesVoucherOption */
    private $stateVoucher;

    /**
     * @param DateTimeImmutable $startDate
     * @param DateTimeImmutable $endDate
     * @param DownloadType|null $downloadType
     */
    public function __construct(DateTimeImmutable $startDate, DateTimeImmutable $endDate, ?DownloadType $downloadType = null)
    {
        $this->setPeriod($startDate, $endDate);
        $this->setDownloadType($this->getDefaultDownloadType($downloadType));
        $this->setComplement(ComplementsOption::todos());
        $this->setStateVoucher(StatesVoucherOption::todos());
        $this->setRfc(new RfcOption(''));
    }

    /**
     * Set the query period using start date and current end date
     *
     * @param DateTimeImmutable $startDate
     * @param DateTimeImmutable $endDate
     * @return $this
     * @throws InvalidArgumentException if start date is greater than end date
     */
    public function setPeriod(DateTimeImmutable $startDate, DateTimeImmutable $endDate): self
    {
        if ($startDate > $endDate) {
            throw InvalidArgumentException::periodStartDateGreaterThanEndDate($startDate, $endDate);
        }
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * Set the query period using specified start date and current end date
     *
     * @param DateTimeImmutable $startDate
     * @return $this
     * @throws InvalidArgumentException if start date is greater than end date
     */
    public function setStartDate(DateTimeImmutable $startDate): self
    {
        return $this->setPeriod($startDate, $this->getEndDate());
    }

    /**
     * @return DateTimeImmutable
     */
    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * Set the query period using current start date and specified end date
     *
     * @param DateTimeImmutable $endDate
     * @return $this
     * @throws InvalidArgumentException if start date is greater than end date
     */
    public function setEndDate(DateTimeImmutable $endDate): self
    {
        return $this->setPeriod($this->getStartDate(), $endDate);
    }

    /**
     * @return RfcOption
     */
    public function getRfc(): RfcOption
    {
        return $this->rfc;
    }

    /**
     * @param RfcOption $rfc
     *
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setStateVoucher(StatesVoucherOption $stateVoucher): self
    {
        $this->stateVoucher = $stateVoucher;

        return $this;
    }
}
