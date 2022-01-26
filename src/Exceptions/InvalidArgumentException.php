<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use DateTimeImmutable;

class InvalidArgumentException extends \InvalidArgumentException implements SatException
{
    public static function emptyInput(string $name): self
    {
        return new self(sprintf('Invalid argument %s is empty', $name));
    }

    public static function periodStartDateGreaterThanEndDate(DateTimeImmutable $start, DateTimeImmutable $end): self
    {
        return new self(
            sprintf('The start date %s is greater than the end date %s', $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')),
        );
    }

    public static function complementsOptionInvalidKey(string $key): self
    {
        return new self("The key '$key' is not registered as a valid option for ComplementsOption");
    }
}
