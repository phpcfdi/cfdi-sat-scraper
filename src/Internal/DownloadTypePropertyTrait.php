<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Internal;

use PhpCfdi\CfdiSatScraper\Filters\DownloadType;

/**
 * This trait contains the methods to insert a $downloadType property
 *
 * @internal
 */
trait DownloadTypePropertyTrait
{
    private DownloadType $downloadType;

    protected function getDefaultDownloadType(?DownloadType $downloadType = null): DownloadType
    {
        return $downloadType ?? DownloadType::emitidos();
    }

    public function getDownloadType(): DownloadType
    {
        return $this->downloadType;
    }

    /**
     * @return $this
     */
    public function setDownloadType(DownloadType $downloadType): self
    {
        $this->downloadType = $downloadType;

        return $this;
    }
}
