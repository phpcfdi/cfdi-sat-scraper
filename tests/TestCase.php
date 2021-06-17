<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests;

use PhpCfdi\CfdiSatScraper\Metadata;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\Tests\FakesFactory\Fakes;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    public static function filePath(string $append = ''): string
    {
        return __DIR__ . '/_files/' . $append;
    }

    public static function fileContentPath(string $append): string
    {
        return static::fileContent(static::filePath($append));
    }

    public static function fileContent(string $path): string
    {
        if (! file_exists($path)) {
            return '';
        }
        return strval(file_get_contents($path));
    }

    public static function fakes(): Fakes
    {
        return new Fakes();
    }

    /**
     * Create an array of Metadata containing the specified uuids.
     * Each Metadata has an urlXml
     *
     * @param string ...$uuids
     * @return array<string, Metadata>
     */
    public static function createMetadataArrayUsingUuids(string ...$uuids): array
    {
        $contents = array_map(function (string $uuid): Metadata {
            return new Metadata($uuid, [ResourceType::xml()->value() => 'https://example.com/' . $uuid]);
        }, $uuids);
        $contents = array_combine($uuids, $contents);
        return $contents ?: [];
    }
}
