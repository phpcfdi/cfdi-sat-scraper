<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use Eclipxe\Enum\Enum;

/**
 * Download type enumerator: recibidos & emitidos.
 *
 * @method static self xml()
 * @method static self pdf()
 * @method static self cancelRequest()
 * @method static self cancelVoucher()
 *
 * @method bool isXml()
 * @method bool isPdf()
 * @method bool isCancelRequest()
 * @method bool isCancelVoucher()
 */
final class ResourceType extends Enum
{
    /** @noinspection PhpMissingParentCallCommonInspection */
    protected static function overrideValues(): array
    {
        return [
            'xml' => 'urlXml',
            'pdf' => 'urlPdf',
            'cancelRequest' => 'urlCancelRequest',
            'cancelVoucher' => 'urlCancelVoucher',
        ];
    }

    public function fileTypeIsXml(): bool
    {
        return $this->isXml();
    }

    public function fileTypeIsPdf(): bool
    {
        return ! $this->isXml();
    }
}
