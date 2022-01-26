<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters\Options;

use Eclipxe\Enum\Enum;
use PhpCfdi\CfdiSatScraper\Contracts\FilterOption;

/**
 * Voucher state option enumerator: todos, cancelados & vigentes.
 *
 * @method static self todos()
 * @method static self cancelados()
 * @method static self vigentes()
 *
 * @method bool isTodos()
 * @method bool isCancelados()
 * @method bool isVigentes()
 */
final class StatesVoucherOption extends Enum implements FilterOption
{
    /** @noinspection PhpMissingParentCallCommonInspection */
    protected static function overrideValues(): array
    {
        return [
            'todos' => '-1',
            'cancelados' => '0',
            'vigentes' => '1',
        ];
    }

    public function nameIndex(): string
    {
        return 'ctl00$MainContent$DdlEstadoComprobante';
    }
}
