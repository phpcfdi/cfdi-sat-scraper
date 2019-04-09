<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters;

use Eclipxe\Enum\Enum;
use PhpCfdi\CfdiSatScraper\Contracts\Filters\FilterOption;

/**
 *
 * @method static self recibidos()
 * @method static self emitidos()
 *
 * @method bool isEmitidos()
 * @method bool isRecibidos()
 */
class DownloadTypes extends Enum implements FilterOption
{
    /**
     * @return array
     */
    protected static function overrideValues(): array
    {
        return [
            'emitidos' => 'RdoTipoBusquedaEmisor',
            'recibidos' => 'RdoTipoBusquedaReceptor',
        ];
    }

    /**
     * @return string
     */
    public function nameIndex(): string
    {
        return 'ctl00$MainContent$TipoBusqueda';
    }
}
