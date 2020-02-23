<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use PhpCfdi\CfdiSatScraper\Filters\DownloadType;

trait DownloadTypePropertyTrait
{
    /** @var DownloadType */
    private $downloadType;

    protected function getDefaultDownloadType(?DownloadType $downloadType = null): DownloadType
    {
        return $downloadType ?? DownloadType::recibidos();
    }

    public function getDownloadType(): DownloadType
    {
        return $this->downloadType;
    }

    /**
     * @param DownloadType $downloadType
     *
     * @return $this
     */
    public function setDownloadType(DownloadType $downloadType)
    {
        $this->downloadType = $downloadType;

        return $this;
    }
}
