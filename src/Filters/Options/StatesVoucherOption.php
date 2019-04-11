<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters\Options;

use Eclipxe\Enum\Enum;
use PhpCfdi\CfdiSatScraper\Contracts\Filters\FilterOption;

/**
 * This is a common use case enum sample
 * source: tests/Fixtures/Stages.php
 *
 * @method static self todos()
 * @method static self cancelados()
 * @method static self vigentes()
 *
 * @method bool isTodos()
 * @method bool isCancelados()
 * @method bool isVigentes()
 */
class StatesVoucherOption extends Enum implements FilterOption
{
    /**
     * @return array
     */
    protected static function overrideValues(): array
    {
        return [
            'todos' => '-1',
            'cancelados' => '0',
            'vigentes' => '1',
        ];
    }

    /**
     * @return string
     */
    public function nameIndex(): string
    {
        return 'ctl00$MainContent$DdlEstadoComprobante';
    }
}
