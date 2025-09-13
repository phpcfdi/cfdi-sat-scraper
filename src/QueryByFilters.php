<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Contracts\QueryInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Filters\Options\ComplementsOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\RfcOnBehalfOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\RfcOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\Internal\DownloadTypePropertyTrait;

/**
 * This class stores all the data to perform a search by filters
 */
class QueryByFilters implements QueryInterface
{
    use DownloadTypePropertyTrait;

    private DateTimeImmutable $startDate;

    private DateTimeImmutable $endDate;

    private RfcOption $rfc;

    private RfcOnBehalfOption $rfcOnBehalf;

    private ComplementsOption $complement;

    private StatesVoucherOption $stateVoucher;

    public function __construct(DateTimeImmutable $startDate, DateTimeImmutable $endDate, ?DownloadType $downloadType = null)
    {
        $this->setPeriod($startDate, $endDate);
        $this->setDownloadType($this->getDefaultDownloadType($downloadType));
        $this->setComplement(ComplementsOption::todos());
        $this->setStateVoucher(StatesVoucherOption::todos());
        $this->setRfc(new RfcOption(''));
        $this->setRfcOnBehalf(new RfcOnBehalfOption(''));
    }

    /**
     * Set the query period using start date and current end date
     *
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

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * Set the query period using specified start date and current end date
     *
     * @return $this
     * @throws InvalidArgumentException if start date is greater than end date
     */
    public function setStartDate(DateTimeImmutable $startDate): self
    {
        return $this->setPeriod($startDate, $this->getEndDate());
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * Set the query period using current start date and specified end date
     *
     * @return $this
     * @throws InvalidArgumentException if start date is greater than end date
     */
    public function setEndDate(DateTimeImmutable $endDate): self
    {
        return $this->setPeriod($this->getStartDate(), $endDate);
    }

    public function getRfc(): RfcOption
    {
        return $this->rfc;
    }

    /**
     * @return $this
     */
    public function setRfc(RfcOption $rfc): self
    {
        $this->rfc = $rfc;

        return $this;
    }

    public function getRfcOnBehalf(): RfcOnBehalfOption
    {
        return $this->rfcOnBehalf;
    }

    /**
     * @return $this
     */
    public function setRfcOnBehalf(RfcOnBehalfOption $rfcOnBehalf): self
    {
        $this->rfcOnBehalf = $rfcOnBehalf;

        return $this;
    }

    public function getComplement(): ComplementsOption
    {
        return $this->complement;
    }

    /**
     * @return $this
     */
    public function setComplement(ComplementsOption $complement): self
    {
        $this->complement = $complement;

        return $this;
    }

    public function getStateVoucher(): StatesVoucherOption
    {
        return $this->stateVoucher;
    }

    /**
     * @return $this
     */
    public function setStateVoucher(StatesVoucherOption $stateVoucher): self
    {
        $this->stateVoucher = $stateVoucher;

        return $this;
    }
}
