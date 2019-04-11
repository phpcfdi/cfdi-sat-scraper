<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use Eclipxe\Enum\Enum;
use PhpCfdi\CfdiSatScraper\Contracts\Filters\FilterOption;
use PhpCfdi\CfdiSatScraper\Contracts\Filters\Options\RfcOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\ComplementsOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;

class Query
{
    /**
     * @var \DateTime
     */
    protected $startDate;

    /**
     * @var \DateTime
     */
    protected $endDate;

    /**
     * @var RfcOption
     */
    protected $rfc;

    /**
     * @var array
     */
    protected $uuid;

    /**
     * @var Enum
     */
    protected $complement;

    /**
     * @var Enum
     */
    protected $stateVoucher;

    /**
     * @var Enum
     */
    protected $downloadType;

    /**
     * Query constructor.
     * @param \DateTimeImmutable $startDate
     * @param \DateTimeImmutable $endDate
     */
    public function __construct(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        if ($endDate < $startDate) {
            throw new \InvalidArgumentException('La fecha final no puede ser menor a la inicial');
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->downloadType = DownloadTypesOption::recibidos();
        $this->complement = ComplementsOption::todos();
        $this->stateVoucher = StatesVoucherOption::todos();
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * @param \DateTimeImmutable $startDate
     * @return Query
     */
    public function setStartDate(\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    /**
     * @param \DateTimeImmutable $endDate
     * @return Query
     */
    public function setEndDate(\DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return RfcOption|null
     */
    public function getRfc(): ?RfcOption
    {
        return $this->rfc;
    }

    /**
     * @param array $uuid
     * @return Query
     */
    public function setUuid(array $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return FilterOption|null
     */
    public function getUuid(): ?array
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
     * @return DownloadTypesOption
     */
    public function getDownloadType(): DownloadTypesOption
    {
        return $this->downloadType;
    }

    /**
     * @param DownloadTypesOption $downloadType
     * @return Query
     */
    public function setDownloadType(DownloadTypesOption $downloadType): self
    {
        $this->downloadType = $downloadType;

        return $this;
    }
}
