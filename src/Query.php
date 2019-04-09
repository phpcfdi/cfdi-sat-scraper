<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSATScraper;

use Eclipxe\Enum\Enum;
use PhpCfdi\CfdiSatScraper\Contracts\Filters\FilterOption;
use PhpCfdi\CfdiSatScraper\Filters\Complements;
use PhpCfdi\CfdiSatScraper\Filters\DownloadTypes;
use PhpCfdi\CfdiSatScraper\Filters\StatesVoucher;

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
     * @var FilterOption
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
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     */
    public function __construct(\DateTime $startDate, \DateTime $endDate)
    {
        if ($endDate < $startDate) {
            throw new \InvalidArgumentException('La fecha final no puede ser menor a la inicial');
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->downloadType = DownloadTypes::recibidos();
        $this->complement = Complements::todos();
        $this->stateVoucher = StatesVoucher::todos();
    }

    /**
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     * @return Query
     */
    public function setStartDate(\DateTime $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     * @return Query
     */
    public function setEndDate(\DateTime $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return FilterOption|null
     */
    public function getRfc(): ?FilterOption
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
     * @param FilterOption $rfc
     *
     * @return Query
     */
    public function setRfc(FilterOption $rfc): self
    {
        $this->rfc = $rfc;

        return $this;
    }

    /**
     * @return Complements
     */
    public function getComplement(): Complements
    {
        return $this->complement;
    }

    /**
     * @param Complements $complement
     * @return Query
     */
    public function setComplement(Complements $complement): self
    {
        $this->complement = $complement;

        return $this;
    }

    /**
     * @return StatesVoucher
     */
    public function getStateVoucher(): StatesVoucher
    {
        return $this->stateVoucher;
    }

    /**
     * @param StatesVoucher $stateVoucher
     * @return Query
     */
    public function setStateVoucher(StatesVoucher $stateVoucher): self
    {
        $this->stateVoucher = $stateVoucher;

        return $this;
    }

    /**
     * @return DownloadTypes
     */
    public function getDownloadType(): DownloadTypes
    {
        return $this->downloadType;
    }

    /**
     * @param DownloadTypes $downloadType
     * @return Query
     */
    public function setDownloadType(DownloadTypes $downloadType): self
    {
        $this->downloadType = $downloadType;

        return $this;
    }
}
