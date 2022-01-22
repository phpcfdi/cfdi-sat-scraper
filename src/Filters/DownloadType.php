<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Filters;

use Eclipxe\Enum\Enum;
use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\URLS;

/**
 * Download type enumerator: recibidos & emitidos.
 *
 * @method static self recibidos()
 * @method static self emitidos()
 *
 * @method bool isEmitidos()
 * @method bool isRecibidos()
 */
final class DownloadType extends Enum
{
    private const URLS = [
        'recibidos' => URLS::PORTAL_CFDI_CONSULTA_RECEPTOR,
        'emitidos' => URLS::PORTAL_CFDI_CONSULTA_EMISOR,
    ];

    public function url(): string
    {
        $url = self::URLS[$this->value()] ?? '';
        if ('' === $url) {
            throw LogicException::generic(sprintf('Enum %s does not have the url for "%s"', self::class, $this->value()));
        }
        return $url;
    }
}
