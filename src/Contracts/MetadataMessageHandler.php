<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

use DateTimeImmutable;

interface MetadataMessageHandler
{
    /**
     * Ocurre cuando se resolvió una consulta entre dos momentos en un mismo día, siempre serán menos de 500 registros.
     */
    public function resolved(DateTimeImmutable $since, DateTimeImmutable $until, int $count): void;

    /**
     * Ocurre cuando se resolvió una consulta de un día determinado.
     * Hay un momento inicial y otro final porque las horas podrían ser diferentes a 00:00:00 y 23:59:59.
     */
    public function date(DateTimeImmutable $since, DateTimeImmutable $until, int $count): void;

    /**
     * Ocurre cuando se encontraron 500 registros en un periodo.
     * Se dividirá la consulta para intentar descargar el contenido completo.
     */
    public function divide(DateTimeImmutable $since, DateTimeImmutable $until): void;

    /**
     * Ocurre cuando se encontraron 500 registros en un solo segundo.
     */
    public function maximum(DateTimeImmutable $moment): void;
}
