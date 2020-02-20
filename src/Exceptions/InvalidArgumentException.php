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

    public static function pathDoesNotExists(string $name, string $path): self
    {
        return new self(sprintf('Invalid argument %s: The path %s does not exists', $name, $path));
    }

    public static function pathIsNotFolder(string $name, string $path): self
    {
        return new self(sprintf('Invalid argument %s: The path %s is not a folder', $name, $path));
    }

    public static function periodStartDateGreaterThanEndDate(DateTimeImmutable $start, DateTimeImmutable $end): self
    {
        return new self(
            sprintf('The start date %s is greater than the end date %s', $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'))
        );
    }

    public static function invalidType(string $name, string $expectedType): self
    {
        return new self("Invalid argument $name is not $expectedType");
    }
}
